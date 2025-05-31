<?php

declare(strict_types=1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require '../includes/database-connection.php';
require '../includes/functions.php';
require '../models/Pelicula.php';
require '../models/Serie.php';
require '../models/Plataforma.php';

// Obtener las últimas 6 películas usando la clase Pelicula
$peliculas = Pelicula::obtenerUltimas($pdo, 5);

// Obtener las últimas 6 series usando la clase Serie
$series = Serie::obtenerUltimas($pdo, 5);

// Obtener todas las plataformas para la navegación
$sql = "SELECT id_plataforma, nombre FROM plataforma";
$navigation = pdo($pdo, $sql)->fetchAll();

$section = '';
$title = 'CONTENT WORLD';
$description = 'El mundo de las peliculas y series';
?>
<?php include '../includes/header.php'; ?>

<?php
$plataformas_favoritas = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Usar la clase Plataforma para obtener las plataformas favoritas del usuario
    $plataformas_favoritas = Plataforma::obtenerFavoritasUsuario($pdo, $user_id);
}
?>

<?php if (!empty($plataformas_favoritas)): ?>
    <section class="header">
        <h1>Mis plataformas favoritas</h1>
    </section>
    <div class="container grid plataformas-favoritas">
        <?php foreach ($plataformas_favoritas as $plataforma): ?>
            <a href="ver_plataforma.php?id_plataforma=<?= $plataforma['id_plataforma'] ?>" class="plataforma-link">
                <div class="card plataforma-card">
                    <?php if (!empty($plataforma['picture'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($plataforma['picture']) ?>" alt="<?= htmlspecialchars($plataforma['nombre']) ?>" class="plataforma-img">
                    <?php endif; ?>
                    <h5><?= htmlspecialchars($plataforma['nombre']) ?></h5>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<section class="header">
    <h1>PELICULAS</h1>
    <p>Peliculas añadidas recientemente</p>
    <br>
</section>
<main class="container peliculas-grid" id="content">
    <?php foreach ($peliculas as $pelicula) { ?>
        <?php $actores_array = explode('|', $pelicula['actores'] ?? ''); ?>
        <article class="pelicula-card">
            <a href="ver_pelicula.php?id_pelicula=<?= $pelicula['id_pelicula'] ?>">
                <img src="../uploads/<?= html_escape($pelicula['image_file'] ?? 'blank.png') ?>" alt="<?= html_escape($pelicula['image_alt']) ?>">
                <div class="pelicula-info">
                    <h2><?= html_escape($pelicula['nombre']) ?></h2>
                    <div class="year"><?= html_escape($pelicula['anio_estreno']) ?></div>
                </div>
            </a>
        </article>
    <?php } ?>
</main>
<section class="header">
    <h1>SERIES</h1>
    <p>Series añadidas recientemente</p>
    <br>
</section>
<main class="container peliculas-grid" id="content">
    <?php foreach ($series as $serie) { ?>
        <?php $actores_array = explode('|', $serie['actores'] ?? ''); ?>
        <article class="pelicula-card">
            <a href="ver_serie.php?id_serie=<?= $serie['id_serie'] ?>">
                <img src="../uploads/<?= html_escape($serie['image_file'] ?? 'blank.png') ?>" alt="<?= html_escape($serie['image_alt']) ?>">
                <div class="pelicula-info">
                    <h2><?= html_escape($serie['nombre']) ?></h2>
                    <div class="year"><?= html_escape($serie['anio_estreno']) ?></div>
                </div>
            </a>
        </article>
    <?php } ?>
</main>
<?php include '../includes/footer.php'; ?>