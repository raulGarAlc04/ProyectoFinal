<?php

declare(strict_types=1);
require '../includes/database-connection.php';
require '../includes/functions.php';
require '../models/Serie.php'; // Incluir la nueva clase

$success = $_GET['success'] ?? null;
$failure = $_GET['failure'] ?? null;

// Usar el método estático de la clase Serie para obtener todas las series
$series = Serie::obtenerTodas($pdo);

?>
<?php include '../includes/admin-header.php'; ?>
<main class="container" id="content">
    <section class="header">
        <h1>Series</h1>
        <?php if ($success) { ?><div class="alert alert-success"><?= $success ?></div><?php } ?>
        <?php if ($failure) { ?><div class="alert alert-danger"><?= $failure ?></div><?php } ?>
        <p><a href="admin_serie.php" class="btn btn-primary">Añadir nueva serie</a></p>
    </section>
    <table>
        <tr>
            <th>Image</th>
            <th>Nombre</th>
            <th class="edit">Editar</th>
            <th class="del">Eliminar</th>
        </tr>
        <?php foreach ($series as $serie) { ?>
            <tr>
                <td><img src="../uploads/<?= html_escape($serie['image_file'] ?? 'blank.png') ?>"
                        alt="<?= html_escape($serie['image_alt'] ?? '') ?>"></td>
                <td><strong><?= html_escape($serie['nombre']) ?></strong></td>
                <td><a href="admin_serie.php?id_serie=<?= $serie['id_serie'] ?>" class="btn btn-primary">Editar</a></td>
                <td><a href="serie-delete.php?id_serie=<?= $serie['id_serie'] ?>" class="btn btn-danger">Eliminar</a></td>
            </tr>
        <?php } ?>
    </table>
</main>
<?php include '../includes/admin-footer.php'; ?>