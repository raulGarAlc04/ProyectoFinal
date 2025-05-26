<?php
include '../includes/database-connection.php';
include '../includes/functions.php';
$section = '';
$title = 'Gestión de Usuarios';
$description = 'Administra los usuarios del sistema';

$uploads = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Variables para el formulario
$usuario = [
    'id' => null,
    'nombre' => '',
    'apellidos' => '',
    'email' => '',
    'password' => '',
    'foto_perfil' => '',
    'es_admin' => 0,
    'activo' => 1
];

// Si estamos editando, cargar el usuario existente
if ($id) {
    $sql = "SELECT * FROM usuarios WHERE id = :id";
    $stmt = pdo($pdo, $sql, ['id' => $id]);
    $usuario_db = $stmt->fetch();
    
    if (!$usuario_db) {
        redirect('listar_usuarios.php', ['failure' => 'Usuario no encontrado']);
    }
    
    // Cargar datos del usuario
    $usuario = [
        'id' => $usuario_db['id'],
        'nombre' => $usuario_db['nombre'],
        'apellidos' => $usuario_db['apellidos'],
        'email' => $usuario_db['email'],
        'password' => '', // No mostramos la contraseña
        'foto_perfil' => $usuario_db['foto_perfil'],
        'es_admin' => $usuario_db['es_admin'],
        'activo' => $usuario_db['activo']
    ];
}

$errores = [
    'warning' => '',
    'nombre' => '',
    'apellidos' => '',
    'email' => '',
    'password' => '',
    'foto_perfil' => '',
];

$file_types = ['image/jpeg', 'image/png', 'image/gif'];
$max_size = 5242880; // 5MB

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger datos del formulario
    $usuario['nombre'] = $_POST['nombre'] ?? '';
    $usuario['apellidos'] = $_POST['apellidos'] ?? '';
    $usuario['email'] = $_POST['email'] ?? '';
    $usuario['password'] = $_POST['password'] ?? '';
    $usuario['es_admin'] = isset($_POST['es_admin']) ? 1 : 0;
    $usuario['activo'] = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones básicas
    if (empty($usuario['nombre'])) {
        $errores['nombre'] = 'El nombre es obligatorio';
    }
    
    if (empty($usuario['apellidos'])) {
        $errores['apellidos'] = 'Los apellidos son obligatorios';
    }
    
    if (empty($usuario['email'])) {
        $errores['email'] = 'El email es obligatorio';
    } elseif (!filter_var($usuario['email'], FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'El email no es válido';
    } else {
        // Verificar que el email no esté en uso por otro usuario
        $sql = "SELECT id FROM usuarios WHERE email = :email AND id != :id";
        $stmt = pdo($pdo, $sql, [
            'email' => $usuario['email'],
            'id' => $id ?? 0
        ]);
        if ($stmt->rowCount() > 0) {
            $errores['email'] = 'Este email ya está en uso';
        }
    }
    
    // Validar contraseña solo si es nuevo usuario o si se está cambiando
    if (!$id && empty($usuario['password'])) {
        $errores['password'] = 'La contraseña es obligatoria para nuevos usuarios';
    } elseif (!empty($usuario['password']) && strlen($usuario['password']) < 6) {
        $errores['password'] = 'La contraseña debe tener al menos 6 caracteres';
    }
    
    // Procesar imagen si se ha subido
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['tmp_name'] && $_FILES['foto_perfil']['error'] === 0) {
        $temp = $_FILES['foto_perfil']['tmp_name'];
        
        // Validar tipo de archivo
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($file_info, $temp);
        finfo_close($file_info);
        
        if (!in_array($mime_type, $file_types)) {
            $errores['foto_perfil'] = 'Formato de imagen no permitido';
        } elseif ($_FILES['foto_perfil']['size'] > $max_size) {
            $errores['foto_perfil'] = 'La imagen es demasiado grande (máximo 5MB)';
        } else {
            // Generar nombre único para la imagen
            $extension = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
            $filename = 'perfil_' . ($id ?? uniqid()) . '_' . uniqid() . '.' . $extension;
            $destination = $uploads . $filename;
            
            try {
                // Procesar y guardar imagen
                $imagick = new \Imagick($temp);
                $imagick->cropThumbnailImage(200, 200); // Recortar a 200x200
                $imagick->writeImage($destination);
                
                // Borrar imagen anterior si existe y no es la predeterminada
                if ($usuario['foto_perfil'] && $usuario['foto_perfil'] !== 'blank.png' && file_exists($uploads . $usuario['foto_perfil'])) {
                    unlink($uploads . $usuario['foto_perfil']);
                }
                
                $usuario['foto_perfil'] = $filename;
            } catch (Exception $e) {
                $errores['foto_perfil'] = 'Error al procesar la imagen: ' . $e->getMessage();
            }
        }
    }
    
    // Si no hay errores, guardar en la base de datos
    if (!array_filter($errores)) {
        try {
            if ($id) {
                // Actualizar usuario existente
                $sql = "UPDATE usuarios SET 
                        nombre = :nombre, 
                        apellidos = :apellidos, 
                        email = :email, 
                        es_admin = :es_admin, 
                        activo = :activo";
                
                $params = [
                    'nombre' => $usuario['nombre'],
                    'apellidos' => $usuario['apellidos'],
                    'email' => $usuario['email'],
                    'es_admin' => $usuario['es_admin'],
                    'activo' => $usuario['activo'],
                    'id' => $id
                ];
                
                // Añadir foto_perfil si se ha actualizado
                if (isset($usuario['foto_perfil']) && $usuario['foto_perfil']) {
                    $sql .= ", foto_perfil = :foto_perfil";
                    $params['foto_perfil'] = $usuario['foto_perfil'];
                }
                
                // Añadir password si se ha actualizado
                if (!empty($usuario['password'])) {
                    $sql .= ", password = :password";
                    $params['password'] = password_hash($usuario['password'], PASSWORD_DEFAULT);
                }
                
                $sql .= " WHERE id = :id";
                
                pdo($pdo, $sql, $params);
                redirect('listar_usuarios.php', ['success' => 'Usuario actualizado correctamente']);
            } else {
                // Crear nuevo usuario
                $sql = "INSERT INTO usuarios (nombre, apellidos, email, password, foto_perfil, es_admin, activo) 
                        VALUES (:nombre, :apellidos, :email, :password, :foto_perfil, :es_admin, :activo)";
                
                $params = [
                    'nombre' => $usuario['nombre'],
                    'apellidos' => $usuario['apellidos'],
                    'email' => $usuario['email'],
                    'password' => password_hash($usuario['password'], PASSWORD_DEFAULT),
                    'foto_perfil' => $usuario['foto_perfil'] ?: 'blank.png',
                    'es_admin' => $usuario['es_admin'],
                    'activo' => $usuario['activo']
                ];
                
                pdo($pdo, $sql, $params);
                redirect('listar_usuarios.php', ['success' => 'Usuario creado correctamente']);
            }
        } catch (PDOException $e) {
            $errores['warning'] = 'Error al guardar: ' . $e->getMessage();
        }
    }
}

include '../includes/admin-header.php';
?>

<main class="container" id="content">
    <h1><?= $id ? 'Editar' : 'Añadir' ?> Usuario</h1>
    
    <?php if ($errores['warning']) { ?>
        <div class="alert alert-danger"><?= $errores['warning'] ?></div>
    <?php } ?>
    
    <form action="admin_usuario.php<?= $id ? '?id=' . $id : '' ?>" method="post" enctype="multipart/form-data">
        <div class="admin-article">
            <section class="image">
                <?php if ($usuario['foto_perfil']) { ?>
                    <label>Foto actual:</label>
                    <img src="../uploads/<?= html_escape($usuario['foto_perfil']) ?>" alt="Foto de perfil">
                <?php } else { ?>
                    <div class="image-placeholder">
                        <p>Sin foto de perfil</p>
                    </div>
                <?php } ?>
                
                <div class="form-group">
                    <label for="foto_perfil">Cambiar foto:</label>
                    <input type="file" name="foto_perfil" id="foto_perfil" class="form-control-file">
                    <?php if ($errores['foto_perfil']) { ?>
                        <div class="alert alert-danger"><?= $errores['foto_perfil'] ?></div>
                    <?php } ?>
                </div>
            </section>
            
            <section class="text">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" name="nombre" id="nombre" value="<?= html_escape($usuario['nombre']) ?>" class="form-control">
                    <?php if ($errores['nombre']) { ?>
                        <div class="alert alert-danger"><?= $errores['nombre'] ?></div>
                    <?php } ?>
                </div>
                
                <div class="form-group">
                    <label for="apellidos">Apellidos:</label>
                    <input type="text" name="apellidos" id="apellidos" value="<?= html_escape($usuario['apellidos']) ?>" class="form-control">
                    <?php if ($errores['apellidos']) { ?>
                        <div class="alert alert-danger"><?= $errores['apellidos'] ?></div>
                    <?php } ?>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" value="<?= html_escape($usuario['email']) ?>" class="form-control">
                    <?php if ($errores['email']) { ?>
                        <div class="alert alert-danger"><?= $errores['email'] ?></div>
                    <?php } ?>
                </div>
                
                <div class="form-group">
                    <label for="password"><?= $id ? 'Nueva contraseña (dejar en blanco para mantener la actual):' : 'Contraseña:' ?></label>
                    <input type="password" name="password" id="password" class="form-control">
                    <?php if ($errores['password']) { ?>
                        <div class="alert alert-danger"><?= $errores['password'] ?></div>
                    <?php } ?>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="es_admin" <?= $usuario['es_admin'] ? 'checked' : '' ?>>
                        Es administrador
                    </label>
                </div>
                <input type="submit" value="Guardar" class="btn btn-primary">
                <a href="listar_usuarios.php" class="btn btn-secondary">Cancelar</a>
            </section>
        </div>
    </form>
</main>

<?php include '../includes/admin-footer.php'; ?>