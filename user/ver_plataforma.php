<?php

declare(strict_types=1);

require '../includes/database-connection.php';
require '../includes/functions.php';
require '../models/Plataforma.php'; // Incluir la nueva clase

$id = filter_input(INPUT_GET, 'id_plataforma', FILTER_VALIDATE_INT);

if (!$id) {
    include './page-not-found.php';
    exit;
}

// Crear un nuevo objeto Plataforma y cargarlo
$plataforma_obj = new Plataforma($pdo);
if (!$plataforma_obj->cargar($id)) {
    include './page-not-found.php';
    exit;
}

// Obtener los datos de la plataforma como un array para facilitar el uso en la plantilla
$plataforma = $plataforma_obj->aArray();

// Obtener películas y series de esta plataforma
$peliculas = $plataforma_obj->obtenerPeliculas();
$series = $plataforma_obj->obtenerSeries();

// Obtener datos de navegación
$sql = "SELECT id_plataforma, nombre FROM plataforma";
$navigation = pdo($pdo, $sql)->fetchAll();
$section = $plataforma['id_plataforma'];
$title = $plataforma['nombre'];
$description = 'Plataforma de contenido';

include '../includes/header.php';

// Obtener ID de usuario
$id_usuario = $_SESSION['user_id'] ?? null;

// Procesar añadir/quitar de favoritos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id_usuario) {
    if (isset($_POST['add_fav'])) {
        $plataforma_obj->agregarAFavoritos($id_usuario);
    } elseif (isset($_POST['remove_fav'])) {
        $plataforma_obj->eliminarDeFavoritos($id_usuario);
    }

    // Redirigir para evitar reenvío del formulario
    header("Location: " . $_SERVER['PHP_SELF'] . "?id_plataforma=" . $id);
    exit;
}

// Verificar si la plataforma es favorita para el usuario actual
$is_favorite = false;
if ($id_usuario) {
    $is_favorite = $plataforma_obj->esFavorita($id_usuario);
}
?>
<main class="container" id="content">
    <section class="header">
        <img src="../uploads/<?= html_escape($plataforma['picture'] ?? 'blank.png') ?>" alt="Logo plataforma">
        <h1><?= html_escape($plataforma['nombre']) ?></h1>
        <p>Precio mensual: <?= html_escape($plataforma['precio_mensual']) ?></p>
        <p>Pais de origen: <?= html_escape($plataforma['pais_origen']) ?></p>
        <p>Año de lanzamiento: <?= html_escape($plataforma['anio_lanzamiento']) ?></p>
        <p>Usuarios activos: <?= html_escape($plataforma['usuarios_activos']) ?></p>

        <?php if ($id_usuario): // Solo mostrar el botón si hay un usuario conectado 
        ?>
            <div class="favorite-button">
                <?php if ($is_favorite): ?>
                    <form method="post">
                        <button type="submit" name="remove_fav" class="btn-remove-fav">Quitar de favoritos</button>
                    </form>
                <?php else: ?>
                    <form method="post">
                        <button type="submit" name="add_fav" class="btn-add-fav">Añadir a favoritos</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="header">
        <h1>PELICULAS</h1>
        <p>Alguna de las peliculas de esta plataforma</p>
        <br>
    </section>
    <section class="grid">
        <?php foreach ($peliculas as $pelicula) { ?>
            <?php $actores_array = explode('|', $pelicula['actores'] ?? ''); ?>
            <article class="summary">
                <a href="ver_pelicula.php?id_pelicula=<?= $pelicula['id_pelicula'] ?>">
                    <img src="../uploads/<?= html_escape($pelicula['image_file'] ?? 'blank.png') ?>" alt="<?= html_escape($pelicula['image_alt']) ?>">
                    <h2><?= html_escape($pelicula['nombre']) ?></h2>
                    <p><?= html_escape($pelicula['anio_estreno']) ?></p>
                </a>
            </article>
        <?php } ?>
    </section>

    <section class="header">
        <h1>SERIES</h1>
        <p>Alguna de las series de esta plataforma</p>
        <br>
    </section>
    <section class="grid">
        <?php foreach ($series as $serie) { ?>
            <?php $actores_array = explode('|', $serie['actores'] ?? ''); ?>
            <article class="summary">
                <a href="ver_serie.php?id_serie=<?= $serie['id_serie'] ?>">
                    <img src="../uploads/<?= html_escape($serie['image_file'] ?? 'blank.png') ?>" alt="<?= html_escape($serie['image_alt']) ?>" class="plataforma-logo">
                    <h2><?= html_escape($serie['nombre']) ?></h2>
                    <p><?= html_escape($serie['anio_estreno']) ?></p>
                </a>
            </article>
        <?php } ?>
    </section>
</main>
<?php include '../includes/footer.php'; ?>