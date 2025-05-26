<?php

declare(strict_types=1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require '../includes/database-connection.php';
require '../includes/functions.php';
require '../models/Plataforma.php';

$terminoBusqueda = $_GET['buscar'] ?? '';
$orden = $_GET['orden'] ?? '';

if ($terminoBusqueda !== '') {
    $plataformas = Plataforma::buscar($pdo, $terminoBusqueda, $orden);
} else {
    $plataformas = Plataforma::filtrar($pdo, $orden);
}

$section = '';
$title = 'Plataformas de Streaming';
$description = 'Explora todas las plataformas de streaming disponibles';
$navigation = [];

include '../includes/header.php';
?>

<main class="container" id="content">
    <header class="plataformas-header">
        <h1><?= $title ?></h1>
        <p><?= $description ?></p>

        <form method="get" action="ver_plataformas.php" class="filtros-peliculas" role="search" aria-label="Buscar y ordenar plataformas">
            <input type="text" name="buscar" placeholder="Buscar por nombre" value="<?= htmlspecialchars($terminoBusqueda) ?>" aria-label="Buscar plataformas por nombre">

            <label for="orden">Ordenar por:</label>
            <select name="orden" id="orden" aria-label="Ordenar plataformas">
                <option value="">-- Selecciona --</option>
                <option value="nombre_asc" <?= ($orden === 'nombre_asc') ? 'selected' : '' ?>>Nombre (A-Z)</option>
                <option value="nombre_desc" <?= ($orden === 'nombre_desc') ? 'selected' : '' ?>>Nombre (Z-A)</option>
                <option value="anio_asc" <?= ($orden === 'anio_asc') ? 'selected' : '' ?>>Año de lanzamiento (ascendente)</option>
                <option value="anio_desc" <?= ($orden === 'anio_desc' || $orden === '') ? 'selected' : '' ?>>Año de lanzamiento (descendente)</option>
            </select>

            <button type="submit">Buscar / Ordenar</button>
        </form>
    </header>

    <div class="plataformas-grid">
        <?php if (count($plataformas) === 0): ?>
            <p>No se encontraron plataformas que coincidan con la búsqueda.</p>
        <?php else: ?>
            <?php foreach ($plataformas as $plataforma) { ?>
                <article class="plataforma-card">
                    <a href="ver_plataforma.php?id_plataforma=<?= $plataforma['id_plataforma'] ?>">
                        <img src="../uploads/<?= html_escape($plataforma['picture'] ?? 'blank.png') ?>"
                             alt="Logo de <?= html_escape($plataforma['nombre']) ?>">
                        <div class="plataforma-info">
                            <h2><?= html_escape($plataforma['nombre']) ?></h2>
                            <div class="year">Desde <?= html_escape($plataforma['anio_lanzamiento']) ?></div>
                            <div class="plataforma-stats">
                                <div class="plataforma-stat">
                                    <span class="plataforma-stat-value"><?= html_escape($plataforma['num_peliculas']) ?></span>
                                    <span>Películas</span>
                                </div>
                                <div class="plataforma-stat">
                                    <span class="plataforma-stat-value"><?= html_escape($plataforma['num_series']) ?></span>
                                    <span>Series</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </article>
            <?php } ?>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>