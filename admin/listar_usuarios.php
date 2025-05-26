<?php
include '../includes/database-connection.php';
include '../includes/functions.php';

include '../includes/admin-header.php';

$section = '';
$title = 'Gestión de Usuarios';
$description = 'Administra los usuarios del sistema';

// Obtener todos los usuarios excepto el usuario conectado
$sql = "SELECT id, nombre, apellidos, email, foto_perfil, fecha_registro, activo, es_admin 
        FROM usuarios 
        WHERE id != :id_actual 
        ORDER BY fecha_registro DESC";
$usuarios = pdo($pdo, $sql, ['id_actual' => $_SESSION['user_id']])->fetchAll();


?>

<main class="container" id="content">
    <h1>Gestión de Usuarios</h1>
    
    <?php if (isset($_GET['success'])) { ?>
        <div class="alert alert-success"><?= html_escape($_GET['success']) ?></div>
    <?php } ?>
    
    <?php if (isset($_GET['failure'])) { ?>
        <div class="alert alert-danger"><?= html_escape($_GET['failure']) ?></div>
    <?php } ?>
    
    <a href="admin_usuario.php" class="btn btn-primary">Añadir Usuario</a>
    
    <table class="admin">
        <thead>
            <tr>
                <th>ID</th>
                <th>Foto</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Fecha Registro</th>
                <th>Estado</th>
                <th>Rol</th>
                <th class="edit">Editar</th>
                <th class="del">Eliminar</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario) { ?>
                <tr>
                    <td><?= $usuario['id'] ?></td>
                    <td>
                        <img src="../uploads/<?= html_escape($usuario['foto_perfil'] ?: 'blank.png') ?>" 
                             alt="Foto de perfil" 
                             style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                    </td>
                    <td><?= html_escape($usuario['nombre'] . ' ' . $usuario['apellidos']) ?></td>
                    <td><?= html_escape($usuario['email']) ?></td>
                    <td><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
                    <td><?= $usuario['activo'] ? 'Activo' : 'Inactivo' ?></td>
                    <td><?= $usuario['es_admin'] ? 'Administrador' : 'Usuario' ?></td>
                    <td><a href="admin_usuario.php?id=<?= $usuario['id'] ?>" class="btn btn-secondary">Editar</a></td>
                    <td><a href="usuario-delete.php?id=<?= $usuario['id'] ?>" class="btn btn-danger">Eliminar</a></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</main>

<?php include '../includes/admin-footer.php'; ?>