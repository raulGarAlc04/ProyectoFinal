<?php

declare(strict_types=1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require '../includes/database-connection.php';
require '../includes/functions.php';
require '../models/Serie.php'; // Incluir la clase Serie

$terminoBusqueda = $_GET['buscar'] ?? '';
$orden = $_GET['orden'] ?? '';

if ($terminoBusqueda !== '') {
    $series = Serie::buscar($pdo, $terminoBusqueda, $orden);
} else {
    $series = Serie::filtrar($pdo, $orden);
}

$section = '';
$title = 'Series';
$description = 'Explora todas las series disponibles en nuestras plataformas';
$navigation = [];  // No necesitamos el menú de plataformas aquí

include '../includes/header.php';
?>

<main class="container" id="content">
    <header class="peliculas-header">
        <h1><?= $title ?></h1>
        <p><?= $description ?></p>

        <form method="get" action="ver_series.php" class="filtros-peliculas" role="search" aria-label="Buscar y ordenar series">
            <input type="text" name="buscar" placeholder="Buscar por nombre" value="<?= htmlspecialchars($terminoBusqueda) ?>" aria-label="Buscar series por nombre">

            <label for="orden">Ordenar por:</label>
            <select name="orden" id="orden" aria-label="Ordenar series">
                <option value="">-- Selecciona --</option>
                <option value="nombre_asc" <?= ($orden === 'nombre_asc') ? 'selected' : '' ?>>Nombre (A-Z)</option>
                <option value="nombre_desc" <?= ($orden === 'nombre_desc') ? 'selected' : '' ?>>Nombre (Z-A)</option>
                <option value="anio_asc" <?= ($orden === 'anio_asc') ? 'selected' : '' ?>>Año de estreno (ascendente)</option>
                <option value="anio_desc" <?= ($orden === 'anio_desc' || $orden === '') ? 'selected' : '' ?>>Año de estreno (descendente)</option>
            </select>

            <button type="submit">Buscar / Ordenar</button>
        </form>
    </header>

    <div class="peliculas-grid">
        <?php if (count($series) === 0): ?>
            <p>No se encontraron series que coincidan con la búsqueda.</p>
        <?php else: ?>
            <?php foreach ($series as $serie) { ?>
                <article class="pelicula-card">
                    <a href="ver_serie.php?id_serie=<?= $serie['id_serie'] ?>">
                        <img src="../uploads/<?= html_escape($serie['image_file'] ?? 'blank.png') ?>"
                             alt="<?= html_escape($serie['image_alt'] ?? $serie['nombre']) ?>">
                        <div class="pelicula-info">
                            <h2><?= html_escape($serie['nombre']) ?></h2>
                            <div class="year"><?= html_escape($serie['anio_estreno']) ?></div>
                        </div>
                    </a>
                </article>
            <?php } ?>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>