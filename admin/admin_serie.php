<?php

declare(strict_types=1);
include '../includes/database-connection.php';
include '../includes/functions.php';
include '../includes/validate.php';
include '../models/Serie.php'; // Incluir la nueva clase

$uploads = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
$id = filter_input(INPUT_GET, 'id_serie', FILTER_VALIDATE_INT);

// Crear un nuevo objeto Serie
$serie_obj = new Serie($pdo, $uploads);

// Si estamos editando, cargar la serie existente
if ($id) {
    if (!$serie_obj->cargar($id)) {
        redirect('listar_series.php', ['failure' => 'Serie no encontrada']);
    }
}

// Obtener datos de la serie como array para el formulario
$serie = $serie_obj->aArray();
$saved_image = $serie['image_file'] ? true : false;

// Obtener plataformas
$sql = "SELECT id_plataforma, nombre FROM plataforma;";
$plataformas = pdo($pdo, $sql)->fetchAll();

// Obtener todos los actores
$sql = "SELECT id_actor, nombre, apellido FROM actor ORDER BY nombre, apellido";
$actores = pdo($pdo, $sql)->fetchAll();

// Obtener actores para esta serie
$actores_serie = $id ? $serie_obj->obtenerIdsActores() : [];

$errores = [
    'warning' => '',
    'nombre' => '',
    'anio_estreno' => '',
    'n_temporadas' => '',
    'plataforma' => '',
    'image_file' => '',
    'image_alt' => '',
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Establecer propiedades de la serie
    $serie_obj->setNombre($_POST['nombre'])
        ->setAnioEstreno((int)$_POST['anio_estreno'])
        ->setNTemporadas((int)$_POST['n_temporadas'])
        ->setIdPlataforma((int)$_POST['id_plataforma']);

    // Manejar carga de imagen
    if (isset($_FILES['image']) && $_FILES['image']['tmp_name'] && $_FILES['image']['error'] === 0) {
        $image_alt = $_POST['image_alt'] ?? '';
        $serie_obj->subirImagen($_FILES['image'], $image_alt);
    }

    // Obtener actores seleccionados
    $actores_seleccionados = $_POST['actores'] ?? [];

    try {
        // Guardar la serie
        $serie_obj->guardar($actores_seleccionados);
        redirect('listar_series.php', ['success' => 'Serie guardada']);
    } catch (Exception $e) {
        if ($e->getCode() === 1) {
            $errores['warning'] = $e->getMessage();
        } else {
            throw $e;
        }
    }
}

include '../includes/admin-header.php';
?>

<form action="admin_serie.php?id_serie=<?= $id ?>" method="post" enctype="multipart/form-data">
    <main class="container admin" id="content">
        <h1>Editar Serie</h1>
        <?php if ($errores['warning']) { ?>
            <div class="alert alert-danger"><?= $errores['warning'] ?></div>
        <?php } ?>

        <div class="admin-article">
            <section class="image">
                <?php if (!$saved_image) { ?>
                    <label for="image">Subir Imagen:</label>
                    <div class="form-group image-placeholder">
                        <input type="file" name="image" class="form-control-file" id="image"><br>
                    </div>
                    <div class="form-group">
                        <label for="image_alt">Texto alternativo: </label>
                        <input type="text" name="image_alt" id="image_alt" value="" class="form-control">
                    </div>
                <?php } else { ?>
                    <label>Imagen:</label>
                    <img src="../uploads/<?= html_escape($serie['image_file']) ?>"
                        alt="<?= html_escape($serie['image_alt']) ?>">
                    <p class="alt"><strong>Texto alternativo:</strong> <?= html_escape($serie['image_alt']) ?></p>
                <?php } ?>
            </section>

            <section class="text">
                <div class="form-group">
                    <label for="nombre">Nombre de la Serie: </label>
                    <input type="text" name="nombre" id="nombre" value="<?= html_escape($serie['nombre']) ?>"
                        class="form-control">
                </div>
                <div class="form-group">
                    <label for="anio_estreno">Año de estreno: </label>
                    <input type="number" name="anio_estreno" id="anio_estreno" value="<?= html_escape($serie['anio_estreno']) ?>"
                        class="form-control">
                </div>
                <div class="form-group">
                    <label for="n_temporadas">Número de temporadas: </label>
                    <input type="number" name="n_temporadas" id="n_temporadas" value="<?= html_escape($serie['n_temporadas']) ?>"
                        class="form-control">
                </div>
                <div class="form-group">
                    <label for="id_plataforma">Plataforma: </label>
                    <select name="id_plataforma" id="plataforma">
                        <?php foreach ($plataformas as $plataforma) { ?>
                            <option value="<?= $plataforma['id_plataforma'] ?>"
                                <?= ($serie['id_plataforma'] == $plataforma['id_plataforma']) ? 'selected' : '' ?>>
                                <?= html_escape($plataforma['nombre']) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="actores">Actores:</label>
                    <select name="actores[]" id="actores" class="form-control" multiple size="6">
                        <?php foreach ($actores as $actor) { ?>
                            <option value="<?= $actor['id_actor'] ?>"
                                <?= in_array($actor['id_actor'], $actores_serie) ? 'selected' : '' ?>>
                                <?= html_escape($actor['nombre'] . ' ' . $actor['apellido']) ?>
                            </option>
                        <?php } ?>
                    </select>
                    <small>Pulsa Ctrl (o Cmd en Mac) para seleccionar varios</small>
                </div>
                <input type="submit" name="actualizar" value="Guardar" class="btn btn-primary">
            </section>
        </div>
    </main>
</form>
<?php include '../includes/admin-footer.php'; ?>