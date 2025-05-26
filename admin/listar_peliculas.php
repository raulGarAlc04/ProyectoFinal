<?php 
declare(strict_types = 1);
require '../includes/database-connection.php';
require '../includes/functions.php';
require '../models/Pelicula.php'; // Incluir la nueva clase

$success = $_GET['success'] ?? null;
$failure = $_GET['failure'] ?? null;

// Usar el método estático de la clase Pelicula para obtener todas las películas
$peliculas = Pelicula::obtenerTodas($pdo);

?>
<?php include '../includes/admin-header.php'; ?>
<main class="container" id="content">
    <section class="header">
        <h1>Peliculas</h1>
        <?php if ($success) { ?><div class="alert alert-success"><?= $success ?></div><?php } ?>
        <?php if ($failure) { ?><div class="alert alert-danger"><?= $failure ?></div><?php } ?>
        <p><a href="admin_pelicula.php" class="btn btn-primary">Añadir nueva pelicula</a></p>
    </section>
    <table>
        <tr>
            <th>Image</th>
            <th>Nombre</th>
            <th class="edit">Editar</th>
            <th class="del">Eliminar</th>
        </tr>
        <?php foreach ($peliculas as $pelicula) { ?>
            <tr>
                <td><img src="../uploads/<?= html_escape($pelicula['image_file'] ?? 'blank.png') ?>" 
                    alt="<?= html_escape($pelicula['image_alt'] ?? '') ?>"></td>
                <td><strong><?= html_escape($pelicula['nombre']) ?></strong></td>
                <td><a href="admin_pelicula.php?id_pelicula=<?= $pelicula['id_pelicula'] ?>" class="btn btn-primary">Editar</a></td>
                <td><a href="pelicula-delete.php?id_pelicula=<?= $pelicula['id_pelicula'] ?>" class="btn btn-danger">Eliminar</a></td>
            </tr>
        <?php } ?>
    </table>
</main>
<?php include '../includes/admin-footer.php'; ?>