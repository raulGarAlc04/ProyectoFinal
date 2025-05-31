<?php

declare(strict_types=1);
require '../includes/database-connection.php';
require '../includes/functions.php';
require '../models/Actor.php';

$id = filter_input(INPUT_GET, 'id_actor', FILTER_VALIDATE_INT);

if (!$id) {
    include './page-not-found.php';
    exit;
}

// Crear un nuevo objeto Actor y cargarlo
$actor_obj = new Actor($pdo);
if (!$actor_obj->cargar($id)) {
    include './page-not-found.php';
    exit;
}

// Obtener los datos del actor como un array para facilitar el uso en la plantilla
$actor = $actor_obj->aArray();

// Obtener películas y series de este actor
$peliculas = $actor_obj->obtenerPeliculas();
$series = $actor_obj->obtenerSeries();

// Obtener datos de navegación
$sql = "SELECT id_plataforma, nombre FROM plataforma";
$navigation = pdo($pdo, $sql)->fetchAll();
$section = '';
$title = $actor['nombre'] . ' ' . $actor['apellido'];
$description = $title . ' en Content World';
?>
<?php include '../includes/header.php'; ?>
<main class="container" id="content">
    <section class="header filmography">
        <h1><?= html_escape($actor['nombre'] . ' ' . $actor['apellido']) ?></h1>
        <p class="member"><b>Fecha de nacimiento: </b><?= format_date($actor['fecha_nacimiento']) ?></p>
        <?php if ($actor['fecha_debut']) { ?>
            <p class="member"><b>Fecha de debut: </b><?= format_date($actor['fecha_debut']) ?></p>
        <?php } ?>
        <p class="member"><b>Estado: </b><?= html_escape($actor['estado_actividad']) ?></p>
        <p class="member"><b>Especialidad: </b><?= html_escape($actor['especialidad']) ?></p>
        <p class="member"><b>Nacionalidad: </b><?= html_escape($actor['nacionalidad']) ?></p>
        <p class="member"><b>Género: </b><?= html_escape($actor['genero']) ?></p>
        <img src="../uploads/<?= html_escape($actor['picture'] ?? 'member.png') ?>"
            alt="<?= html_escape($actor['nombre']) ?>" class="profile"><br>
    </section>
    <section class="header">
        <h1>PELICULAS</h1>
        <p>Alguna de las peliculas de este actor</p>
        <br>
    </section>
    <section class="grid">
        <?php foreach ($peliculas as $pelicula) { ?>
            <?php $actores_array = explode('|', $pelicula['actores']); ?>
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
        <p>Alguna de las series de este actor</p>
        <br>
    </section>
    <section class="grid">
        <?php foreach ($series as $serie) { ?>
            <?php $actores_array = explode('|', $serie['actores'] ?? ''); ?>
            <article class="summary">
                <a href="ver_serie.php?id_serie=<?= $serie['id_serie'] ?>">
                    <img src="../uploads/<?= html_escape($serie['image_file'] ?? 'blank.png') ?>" alt="<?= html_escape($serie['image_alt']) ?>">
                    <h2><?= html_escape($serie['nombre']) ?></h2>
                    <p><?= html_escape($serie['anio_estreno']) ?></p>
                </a>
            </article>
        <?php } ?>
    </section>
</main>
<?php include '../includes/footer.php'; ?>