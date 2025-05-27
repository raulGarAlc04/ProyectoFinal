<?php
include '../includes/database-connection.php';
include '../includes/functions.php';
include '../includes/validate.php';
include '../models/Pelicula.php'; // Incluir la nueva clase

$uploads = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
$id = filter_input(INPUT_GET, 'id_pelicula', FILTER_VALIDATE_INT);

// Crear un nuevo objeto Pelicula
$pelicula_obj = new Pelicula($pdo, $uploads);

// Si estamos editando, cargar la película existente
if ($id) {
    if (!$pelicula_obj->cargar($id)) {
        redirect('listar_peliculas.php', ['failure' => 'Pelicula no encontrada']);
    }
}

// Obtener datos de la película como array para el formulario
$pelicula = $pelicula_obj->aArray();
$saved_image = $pelicula['image_file'] ? true : false;

// Obtener plataformas
$sql = "SELECT id_plataforma, nombre FROM plataforma;";
$plataformas = pdo($pdo, $sql)->fetchAll();

// Obtener todos los actores
$sql = "SELECT id_actor, nombre, apellido FROM actor ORDER BY nombre, apellido";
$actores = pdo($pdo, $sql)->fetchAll();

// Obtener actores para esta película
$actores_pelicula = $id ? $pelicula_obj->obtenerIdsActores() : [];

$errores = [
    'warning' => '',
    'nombre' => '',
    'anio_estreno' => '',
    'director' => '',
    'plataforma' => '',
    'image_file' => '',
    'image_alt' => '',
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Establecer propiedades de la película
    $pelicula_obj->setNombre($_POST['nombre'])
        ->setAnioEstreno((int)$_POST['anio_estreno'])
        ->setDirector($_POST['director'])
        ->setIdPlataforma((int)$_POST['id_plataforma']);

    // Manejar carga de imagen - Verificar si se ha subido una imagen
    if (isset($_FILES['image']) && $_FILES['image']['tmp_name'] && $_FILES['image']['error'] === 0) {
        $image_alt = $_POST['image_alt'] ?? '';
        $pelicula_obj->subirImagen($_FILES['image'], $image_alt);
    }

    // Obtener actores seleccionados
    $actores_seleccionados = $_POST['actores'] ?? [];

    try {
        // Guardar la película
        $pelicula_obj->guardar($actores_seleccionados);
        redirect('listar_peliculas.php', ['success' => 'Película guardada']);
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

<form action="admin_pelicula.php?id_pelicula=<?= $id ?>" method="post" enctype="multipart/form-data">
    <main class="container admin" id="content">
        <h1>Editar Película</h1>
        <?php if ($errores['warning']) { ?>
            <div class="alert alert-danger"><?= $errores['warning'] ?></div>
        <?php } ?>

        <div class="admin-article">
            <section class="image">
                <?php if ($saved_image) { ?>
                    <label>Imagen actual:</label>
                    <img src="../uploads/<?= html_escape($pelicula['image_file']) ?>"
                        alt="<?= html_escape($pelicula['image_alt']) ?>">
                    <p class="alt"><strong>Texto alternativo:</strong> <?= html_escape($pelicula['image_alt']) ?></p>

                    <div class="form-group">
                        <label for="image">Cambiar imagen:</label>
                        <input type="file" name="image" class="form-control-file" id="image"><br>
                    </div>
                    <div class="form-group">
                        <label for="image_alt">Nuevo texto alternativo:</label>
                        <input type="text" name="image_alt" id="image_alt" value="<?= html_escape($pelicula['image_alt']) ?>" class="form-control">
                    </div>
                <?php } else { ?>
                    <label for="image">Subir Imagen:</label>
                    <div class="form-group image-placeholder">
                        <input type="file" name="image" class="form-control-file" id="image"><br>
                    </div>
                    <div class="form-group">
                        <label for="image_alt">Texto alternativo: </label>
                        <input type="text" name="image_alt" id="image_alt" value="" class="form-control">
                    </div>
                <?php } ?>
            </section>

            <section class="text">
                <div class="form-group">
                    <label for="nombre">Nombre de la Película: </label>
                    <input type="text" name="nombre" id="nombre" value="<?= html_escape($pelicula['nombre']) ?>"
                        class="form-control">
                </div>
                <div class="form-group">
                    <label for="anio_estreno">Año de estreno: </label>
                    <input type="number" name="anio_estreno" id="anio_estreno" value="<?= html_escape($pelicula['anio_estreno']) ?>"
                        class="form-control">
                </div>
                <div class="form-group">
                    <label for="director">Director: </label>
                    <input type="text" name="director" id="director" value="<?= html_escape($pelicula['director']) ?>"
                        class="form-control">
                </div>
                <div class="form-group">
                    <label for="id_plataforma">Plataforma: </label>
                    <select name="id_plataforma" id="plataforma">
                        <?php foreach ($plataformas as $plataforma) { ?>
                            <option value="<?= $plataforma['id_plataforma'] ?>"
                                <?= ($pelicula['id_plataforma'] == $plataforma['id_plataforma']) ? 'selected' : '' ?>>
                                <?= html_escape($plataforma['nombre']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="actores">Actores:</label>
                    <select name="actores[]" id="actores" class="form-control" multiple size="6">
                        <?php foreach ($actores as $actor) { ?>
                            <option value="<?= $actor['id_actor'] ?>"
                                <?= in_array($actor['id_actor'], $actores_pelicula) ? 'selected' : '' ?>>
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