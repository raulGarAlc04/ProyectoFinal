<?php

declare(strict_types=1);
require '../includes/database-connection.php';
require '../includes/functions.php';
require '../models/Actor.php';

$success = $_GET['success'] ?? null;
$failure = $_GET['failure'] ?? null;

// Usar el método estático de la clase Actor para obtener todos los actores
$actores = Actor::obtenerTodos($pdo);
?>
<?php include '../includes/admin-header.php'; ?>
<main class="container" id="content">
    <section class="header">
        <h1>Actores</h1>
        <?php if ($success) { ?><div class="alert alert-success"><?= $success ?></div><?php } ?>
        <?php if ($failure) { ?><div class="alert alert-danger"><?= $failure ?></div><?php } ?>
        <p><a href="admin_actor.php" class="btn btn-primary">Añadir nuevo actor</a></p>
    </section>
    <table>
        <tr>
            <th>Foto</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Fecha Nacimiento</th>
            <th>Nacionalidad</th>
            <th>Género</th>
            <th>Estado</th>
            <th class="edit">Editar</th>
            <th class="del">Eliminar</th>
        </tr>
        <?php foreach ($actores as $actor) { ?>
            <tr>
                <td>
                    <img src="../uploads/<?= html_escape($actor['picture'] ?? 'blank.png') ?>"
                        alt="<?= html_escape($actor['nombre'] . ' ' . $actor['apellido']) ?>" style="width:60px;height:60px;object-fit:cover;">
                </td>
                <td><strong><?= html_escape($actor['nombre']) ?></strong></td>
                <td><?= html_escape($actor['apellido']) ?></td>
                <td><?= html_escape($actor['fecha_nacimiento']) ?></td>
                <td><?= html_escape($actor['nacionalidad']) ?></td>
                <td><?= html_escape($actor['genero']) ?></td>
                <td><?= html_escape($actor['estado_actividad']) ?></td>
                <td><a href="admin_actor.php?id_actor=<?= $actor['id_actor'] ?>" class="btn btn-primary">Editar</a></td>
                <td><a href="actor-delete.php?id_actor=<?= $actor['id_actor'] ?>" class="btn btn-danger">Eliminar</a></td>
            </tr>
        <?php } ?>
    </table>
</main>
<?php include '../includes/admin-footer.php'; ?>