<?php
include '../includes/database-connection.php';
include '../includes/functions.php';
$section = '';
$title = 'Películas';
$description = 'Explora todas las películas disponibles en nuestras plataformas';
include '../includes/admin-header.php';
?>

<?php
if (!isset($_SESSION['user_id'])) {
    redirect('login.php', ['failure' => 'Debes iniciar sesión']);
}

$user_id = $_SESSION['user_id'];
$uploads = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
$file_types = ['image/jpeg', 'image/png', 'image/gif'];
$max_size = 5242880; // 5MB

// Obtener datos del usuario
$sql = "SELECT nombre, apellidos, email, foto_perfil FROM usuarios WHERE id = :id";
$user = pdo($pdo, $sql, ['id' => $user_id])->fetch();

$errores = [
    'warning' => '',
    'foto_perfil' => '',
];

$temp = $_FILES['foto_perfil']['tmp_name'] ?? '';
$destination = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Manejar borrado de cuenta
    if (isset($_POST['borrar_cuenta'])) {
        // Borrar foto de perfil si existe
        if ($user['foto_perfil'] && file_exists($uploads . $user['foto_perfil']) && $user['foto_perfil'] !== 'blank.png') {
            unlink($uploads . $user['foto_perfil']);
        }

        // Borrar usuario de la base de datos
        $sql = "DELETE FROM usuarios WHERE id = :id";
        pdo($pdo, $sql, ['id' => $user_id]);

        // Destruir sesión
        session_destroy();

        // Redirigir a página de inicio
        redirect('../index.php', ['success' => 'Cuenta eliminada correctamente']);
    }

    if ($temp && $_FILES['foto_perfil']['error'] === 0) {
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($file_info, $temp);
        finfo_close($file_info);

        if (!in_array($mime_type, $file_types)) {
            $errores['foto_perfil'] = 'Formato de imagen no permitido.';
        } elseif ($_FILES['foto_perfil']['size'] > $max_size) {
            $errores['foto_perfil'] = 'La imagen es demasiado grande.';
        } else {
            $extension = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
            $filename = 'perfil_' . $user_id . '_' . uniqid() . '.' . $extension;
            $destination = $uploads . $filename;

            try {
                $imagick = new \Imagick($temp);
                $imagick->cropThumbnailImage(200, 200);
                $imagick->writeImage($destination);

                // Borrar la anterior si existe y no es la de por defecto
                if ($user['foto_perfil'] && file_exists($uploads . $user['foto_perfil']) && $user['foto_perfil'] !== 'blank.png') {
                    unlink($uploads . $user['foto_perfil']);
                }

                // Actualizar en la base de datos
                $sql = "UPDATE usuarios SET foto_perfil = :foto_perfil WHERE id = :id";
                pdo($pdo, $sql, ['foto_perfil' => $filename, 'id' => $user_id]);

                // Actualizar en sesión (después del header)
                $_SESSION['foto_perfil'] = $filename;

                // Refrescar datos
                $user['foto_perfil'] = $filename;
            } catch (Exception $e) {
                $errores['warning'] = 'Error al procesar la imagen.';
            }
        }
    }
}
?>

<main class="container" id="content">
    <h1>Mi Perfil</h1>
    <?php if ($errores['warning']) { ?>
        <div class="alert alert-danger"><?= $errores['warning'] ?></div>
    <?php } ?>
    <div class="profile-section">
        <form action="admin_perfil.php" method="post" enctype="multipart/form-data">
            <div class="profile-image">
                <img src="../uploads/<?= htmlspecialchars($user['foto_perfil'] ?? 'blank.png') ?>" alt="Foto de perfil" class="profile-img">
            </div>
            <div class="form-group">
                <label for="foto_perfil">Cambiar foto de perfil:</label>
                <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*">
                <?php if ($errores['foto_perfil']) { ?>
                    <div class="alert alert-danger"><?= $errores['foto_perfil'] ?></div>
                <?php } ?>
            </div>
            <input type="submit" value="Actualizar foto" class="btn btn-primary">
        </form>

        <div class="profile-info">
            <p><strong>Nombre:</strong> <?= htmlspecialchars($user['nombre']) . ' ' . htmlspecialchars($user['apellidos']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        </div>

        <div class="profile-actions">
            <a href="../logout.php?user_id=<?php echo $_SESSION['user_id']; ?>" class="btn btn-secondary">
                Cerrar Sesión
            </a>
        </div>
    </div>
</main>
<?php include '../includes/admin-footer.php'; ?>