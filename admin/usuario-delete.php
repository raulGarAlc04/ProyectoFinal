<?php
include '../includes/database-connection.php';
include '../includes/functions.php';
$section = '';
$title = 'Eliminar Usuario';
$description = 'Eliminar usuario del sistema';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    redirect('listar_usuarios.php', ['failure' => 'ID de usuario no válido']);
}

// Obtener datos del usuario
$sql = "SELECT id, nombre, apellidos, email, foto_perfil FROM usuarios WHERE id = :id";
$usuario = pdo($pdo, $sql, ['id' => $id])->fetch();

if (!$usuario) {
    redirect('listar_usuarios.php', ['failure' => 'Usuario no encontrado']);
}

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Eliminar foto de perfil si existe y no es la predeterminada
        $uploads = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        if ($usuario['foto_perfil'] && $usuario['foto_perfil'] !== 'blank.png' && file_exists($uploads . $usuario['foto_perfil'])) {
            unlink($uploads . $usuario['foto_perfil']);
        }
        
        // Eliminar usuario
        $sql = "DELETE FROM usuarios WHERE id = :id";
        pdo($pdo, $sql, ['id' => $id]);
        
        redirect('listar_usuarios.php', ['success' => 'Usuario eliminado correctamente']);
    } catch (PDOException $e) {
        // Si hay un error, mostrar mensaje
        $error = 'Error al eliminar el usuario: ' . $e->getMessage();
    }
}

include '../includes/admin-header.php';
?>

<main class="container" id="content">
    <h1>Eliminar Usuario</h1>
    
    <?php if (isset($error)) { ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php } ?>
    
    <div class="alert alert-danger">
        <p><strong>¿Estás seguro de que deseas eliminar este usuario?</strong></p>
        <p>Esta acción no se puede deshacer.</p>
    </div>
    
    <div class="admin-article">
        <section class="image">
            <img src="../uploads/<?= html_escape($usuario['foto_perfil'] ?: 'blank.png') ?>" alt="Foto de perfil" style="max-width: 200px; border-radius: 50%;">
        </section>
        
        <section class="text">
            <p><strong>Nombre:</strong> <?= html_escape($usuario['nombre'] . ' ' . $usuario['apellidos']) ?></p>
            <p><strong>Email:</strong> <?= html_escape($usuario['email']) ?></p>
            
            <form method="post">
                <input type="submit" value="Eliminar Usuario" class="btn btn-danger">
                <a href="listar_usuarios.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </section>
    </div>
</main>

<?php include '../includes/admin-footer.php'; ?>