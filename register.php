<?php

declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require './includes/database-connection.php';
require './includes/functions.php';

// Inicializar variables
$usuario = [
    'nombre' => '',
    'apellidos' => '',
    'email' => '',
    'password' => '',
];

$error = '';
$uploads = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
$file_types = ['image/jpeg', 'image/png', 'image/gif'];
$max_size = 5242880; // 5MB

// Función para limpiar nombres de archivo (igual que en Actor.php)
function limpiarNombreArchivo(string $nombre): string
{
    // Convertir a minúsculas
    $nombre = strtolower($nombre);

    // Reemplazar caracteres especiales y espacios
    $nombre = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'], ['a', 'e', 'i', 'o', 'u', 'n', 'u'], $nombre);
    $nombre = preg_replace('/[^a-z0-9_-]/', '_', $nombre);

    // Eliminar múltiples guiones bajos consecutivos
    $nombre = preg_replace('/_+/', '_', $nombre);

    // Eliminar guiones bajos al inicio y final
    $nombre = trim($nombre, '_');

    return $nombre;
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario
    $usuario['nombre'] = $_POST['nombre'] ?? '';
    $usuario['apellidos'] = $_POST['apellidos'] ?? '';
    $usuario['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $usuario['password'] = $_POST['password'] ?? '';

    // Validar que los campos no estén vacíos
    if (
        empty($usuario['nombre']) || empty($usuario['apellidos']) ||
        empty($usuario['email']) || empty($usuario['password'])
    ) {
        $error = "Por favor, completa todos los campos.";
    } else {
        // Verificar si el email ya existe
        $sql = "SELECT id FROM usuarios WHERE email = :email";
        $user = pdo($pdo, $sql, [':email' => $usuario['email']])->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $error = "Este email ya está registrado.";
        } else {
            try {
                $pdo->beginTransaction();

                // Procesar foto de perfil si se subió
                $foto_perfil = 'blank.png'; // Por defecto
                $temp = $_FILES['foto_perfil']['tmp_name'] ?? '';
                
                if ($temp && $_FILES['foto_perfil']['error'] === 0) {
                    // Verificar que el directorio uploads existe
                    if (!is_dir($uploads)) {
                        mkdir($uploads, 0755, true);
                    }
                    
                    $file_info = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($file_info, $temp);
                    finfo_close($file_info);

                    if (!in_array($mime_type, $file_types)) {
                        throw new Exception('Formato de imagen no permitido. Use JPG, PNG o GIF.');
                    } elseif ($_FILES['foto_perfil']['size'] > $max_size) {
                        throw new Exception('La imagen es demasiado grande. Máximo 5MB.');
                    } else {
                        // Generar nombre basado en el nombre del usuario con prefijo "perfil_"
                        $nombre_limpio = limpiarNombreArchivo($usuario['nombre'] . '_' . $usuario['apellidos']);
                        $extension = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
                        $filename = 'perfil_' . $nombre_limpio . '.' . $extension;

                        // Asegurar que el nombre sea único
                        $contador = 1;
                        while (file_exists($uploads . $filename)) {
                            $filename = 'perfil_' . $nombre_limpio . '_' . $contador . '.' . $extension;
                            $contador++;
                        }

                        $destination = $uploads . $filename;

                        try {
                            $imagick = new \Imagick($temp);
                            $imagick->cropThumbnailImage(200, 200); // Tamaño cuadrado para foto de perfil
                            $imagick->writeImage($destination);
                            $foto_perfil = $filename;
                        } catch (Exception $e) {
                            if (file_exists($destination)) {
                                unlink($destination);
                            }
                            throw new Exception('Error al procesar la imagen.');
                        }
                    }
                }

                // Preparar la inserción con los valores por defecto para fecha, activo y es_admin
                $sql = "INSERT INTO usuarios (nombre, apellidos, email, password, fecha_registro, activo, es_admin, foto_perfil) 
                        VALUES (:nombre, :apellidos, :email, :password, NOW(), 1, 0, :foto_perfil)";

                $params = [
                    ':nombre' => $usuario['nombre'],
                    ':apellidos' => $usuario['apellidos'],
                    ':email' => $usuario['email'],
                    ':password' => $usuario['password'], // En producción usar password_hash()
                    ':foto_perfil' => $foto_perfil
                ];

                pdo($pdo, $sql, $params);

                // Obtener el ID del usuario recién creado
                $user_id = $pdo->lastInsertId();

                $pdo->commit();

                // Iniciar sesión para el usuario recién registrado
                session_start();
                $_SESSION['user_id'] = (int)$user_id;
                $_SESSION['user_name'] = $usuario['nombre'];
                $_SESSION['is_admin'] = 0; // Por defecto, no es admin
                $_SESSION['foto_perfil'] = $foto_perfil; // Establecer foto

                // Redirigir al usuario a la página correspondiente con el ID en la URL
                if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
                    redirect('./admin/index.php?user_id=' . $user_id, ['success' => 'Cuenta creada con éxito']);
                } else {
                    redirect('./user/index.php?user_id=' . $user_id, ['success' => 'Cuenta creada con éxito']);
                }
            } catch (Exception $e) {
                $pdo->rollBack();

                if (($e instanceof PDOException) and ($e->errorInfo[1] === 1062)) {
                    $error = 'Email ya registrado';
                } else {
                    $error = 'Error al crear la cuenta: ' . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta</title>
    <link rel="stylesheet" type="text/css" href="./css/login_styles.css">
</head>

<body>
    <div class="register-container">
        <h2>Crear Cuenta</h2>

        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?= html_escape($usuario['nombre']) ?>" required>
            </div>

            <div class="form-group">
                <label for="apellidos">Apellidos:</label>
                <input type="text" id="apellidos" name="apellidos" value="<?= html_escape($usuario['apellidos']) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= html_escape($usuario['email']) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="foto_perfil">Foto de perfil (opcional):</label>
                <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*">
                <small>Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB</small>
            </div>

            <button type="submit">Crear Cuenta</button>
        </form>

        <div class="links">
            <a href="index.php">¿Ya tienes cuenta? Iniciar Sesión</a>
        </div>
    </div>
</body>

</html>