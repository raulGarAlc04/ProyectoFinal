<?php
declare(strict_types = 1);
require '../includes/database-connection.php';
require '../includes/functions.php';

$sql = "SELECT count(id_pelicula) FROM pelicula";
$pelicula_count = pdo($pdo, $sql)->fetchColumn();
$sql = "SELECT count(id_serie) FROM serie";
$serie_count = pdo($pdo, $sql)->fetchColumn();
$sql = "SELECT count(id_plataforma) FROM plataforma";
$plataforma_count = pdo($pdo, $sql)->fetchColumn();
$sql = "SELECT count(id_actor) FROM actor";
$actor_count = pdo($pdo, $sql)->fetchColumn();
$sql = "SELECT count(id) FROM usuarios";
$usuarios_count = pdo($pdo, $sql)->fetchColumn();
?>
<?php include '../includes/admin-header.php'; ?>
<main class="container" id="content">
    <section class="header">
        <h1>BIENVENIDO AL PANEL DE ADMINISTRACIÓN</h1>
    </section>
    <table class="admin">
        <tr>
            <th></th>
            <th>Cantidad</th>
            <th class="create">Crear</th>
            <th class="view">Ver</th>
        </tr>
        <tr>
            <td><strong>Peliculas</strong></td>
            <td><?= $pelicula_count ?></td>
            <td><a href="admin_pelicula.php" class="btn btn-primary">Añadir</a></td>
            <td><a href="listar_peliculas.php" class="btn btn-primary">Ver</a></td>
        </tr>
        <tr>
            <td><strong>Series</strong></td>
            <td><?= $serie_count ?></td>
            <td><a href="admin_serie.php" class="btn btn-primary">Añadir</a></td>
            <td><a href="listar_series.php" class="btn btn-primary">Ver</a></td>
        </tr>
        <tr>
            <td><strong>Plataformas</strong></td>
            <td><?= $plataforma_count ?></td>
            <td><a href="admin_plataforma.php" class="btn btn-primary">Añadir</a></td>
            <td><a href="listar_plataformas.php" class="btn btn-primary">Ver</a></td>
        </tr>
        <tr>
            <td><strong>Actores</strong></td>
            <td><?= $actor_count ?></td>
            <td><a href="admin_actor.php" class="btn btn-primary">Añadir</a></td>
            <td><a href="listar_actores.php" class="btn btn-primary">Ver</a></td>
        </tr>
        <tr>
            <td><strong>Usuarios</strong></td>
            <td><?= $usuarios_count ?></td>
            <td><a href="admin_usuario.php" class="btn btn-primary">Añadir</a></td>
            <td><a href="listar_usuarios.php" class="btn btn-primary">Ver</a></td>
        </tr>
    </table>
</main>
<?php include '../includes/admin-footer.php'; ?>