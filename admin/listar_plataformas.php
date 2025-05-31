<?php

declare(strict_types=1);
require '../includes/database-connection.php';
require '../includes/functions.php';
require '../models/Plataforma.php';

$success = $_GET['success'] ?? null;
$failure = $_GET['failure'] ?? null;

// Usar el método estático de la clase Plataforma para obtener todas las plataformas con conteo de contenido
$plataformas = Plataforma::obtenerTodasConContenido($pdo);
?>
<?php include '../includes/admin-header.php'; ?>
<main class="container" id="content">
    <section class="header">
        <h1>Plataformas</h1>
        <?php if ($success) { ?><div class="alert alert-success"><?= $success ?></div><?php } ?>
        <?php if ($failure) { ?><div class="alert alert-danger"><?= $failure ?></div><?php } ?>
        <p><a href="admin_plataforma.php" class="btn btn-primary">Añadir nueva plataforma</a></p>
    </section>
    <table>
        <tr>
            <th>Logo</th>
            <th>Nombre</th>
            <th>Precio</th>
            <th>País</th>
            <th>Contenido</th>
            <th class="edit">Editar</th>
            <th class="del">Eliminar</th>
        </tr>
        <?php foreach ($plataformas as $plataforma) { ?>
            <tr>
                <td><img src="../uploads/<?= html_escape($plataforma['picture'] ?? 'blank.png') ?>"
                        alt="Logo de <?= html_escape($plataforma['nombre']) ?>"></td>
                <td><strong><?= html_escape($plataforma['nombre']) ?></strong></td>
                <td><?= html_escape($plataforma['precio_mensual']) ?> €</td>
                <td><?= html_escape($plataforma['pais_origen']) ?></td>
                <td>
                    <span title="Películas"><?= $plataforma['num_peliculas'] ?> 🎬</span> |
                    <span title="Series"><?= $plataforma['num_series'] ?> 📺</span>
                </td>
                <td><a href="admin_plataforma.php?id_plataforma=<?= $plataforma['id_plataforma'] ?>" class="btn btn-primary">Editar</a></td>
                <td><a href="plataforma-delete.php?id_plataforma=<?= $plataforma['id_plataforma'] ?>" class="btn btn-danger">Eliminar</a></td>
            </tr>
        <?php } ?>
    </table>
</main>
<?php include '../includes/admin-footer.php'; ?>