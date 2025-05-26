<?php
require '../includes/database-connection.php';
require '../includes/functions.php';
require '../models/Pelicula.php';

$terminoBusqueda = $_GET['buscar'] ?? '';
$orden = $_GET['orden'] ?? '';

if ($terminoBusqueda !== '') {
    $peliculas = Pelicula::buscar($pdo, $terminoBusqueda, $orden);
} else {
    $peliculas = Pelicula::filtrar($pdo, $orden);
}

$section = '';
$title = 'Películas';
$description = 'Explora todas las películas disponibles en nuestras plataformas';
$navigation = [];

include '../includes/header.php';
?>

<main class="container" id="content">
    <header class="peliculas-header">
        <h1><?= $title ?></h1>
        <p><?= $description ?></p>

        <form method="get" action="ver_peliculas.php" class="filtros-peliculas" role="search" aria-label="Buscar y ordenar películas">
            <input type="text" name="buscar" placeholder="Buscar por nombre" value="<?= htmlspecialchars($terminoBusqueda) ?>" aria-label="Buscar películas por nombre">

            <label for="orden">Ordenar por:</label>
            <select name="orden" id="orden" aria-label="Ordenar películas">
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
        <?php if (count($peliculas) === 0): ?>
            <p>No se encontraron películas que coincidan con la búsqueda.</p>
        <?php else: ?>
            <?php foreach ($peliculas as $pelicula) { ?>
                <article class="pelicula-card">
                    <a href="ver_pelicula.php?id_pelicula=<?= $pelicula['id_pelicula'] ?>">
                        <img src="../uploads/<?= html_escape($pelicula['image_file'] ?? 'blank.png') ?>"
                            alt="<?= html_escape($pelicula['image_alt'] ?? $pelicula['nombre']) ?>">
                        <div class="pelicula-info">
                            <h2><?= html_escape($pelicula['nombre']) ?></h2>
                            <div class="year"><?= html_escape($pelicula['anio_estreno']) ?></div>
                        </div>
                    </a>
                </article>
            <?php } ?>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>