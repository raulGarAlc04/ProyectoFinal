<?php

declare(strict_types=1);
include '../includes/database-connection.php';
include '../includes/functions.php';
include '../includes/validate.php';
include '../models/Plataforma.php'; // Incluir la nueva clase

$uploads = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
$id = filter_input(INPUT_GET, 'id_plataforma', FILTER_VALIDATE_INT);

// Crear un nuevo objeto Plataforma
$plataforma_obj = new Plataforma($pdo, $uploads);

// Si estamos editando, cargar la plataforma existente
if ($id) {
    if (!$plataforma_obj->cargar($id)) {
        redirect('listar_plataformas.php', ['failure' => 'Plataforma no encontrada']);
    }
}

// Obtener datos de la plataforma como array para el formulario
$plataforma = $plataforma_obj->aArray();
$saved_image = $plataforma['picture'] ? true : false;

$errores = [
    'warning' => '',
    'nombre' => '',
    'precio_mensual' => '',
    'pais_origen' => '',
    'anio_lanzamiento' => '',
    'usuarios_activos' => '',
    'image_file' => '',
    'image_alt' => '',
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Establecer propiedades de la plataforma
    $plataforma_obj->setNombre($_POST['nombre'])
        ->setPrecioMensual((float)$_POST['precio_mensual'])
        ->setPaisOrigen($_POST['pais_origen'])
        ->setAnioLanzamiento((int)$_POST['anio_lanzamiento'])
        ->setUsuariosActivos((int)$_POST['usuarios_activos']);

    // Manejar carga de imagen
    if (isset($_FILES['image']) && $_FILES['image']['tmp_name'] && $_FILES['image']['error'] === 0) {
        $plataforma_obj->subirImagen($_FILES['image']);
    }

    try {
        // Guardar la plataforma
        $plataforma_obj->guardar();
        redirect('listar_plataformas.php', ['success' => 'Plataforma guardada']);
    } catch (Exception $e) {
        if ($e->getCode() === 1) {
            $errores['warning'] = $e->getMessage();
        } else {
            $errores['warning'] = 'Error: ' . $e->getMessage();
        }
    }
}

include '../includes/admin-header.php';
?>

<form action="admin_plataforma.php?id_plataforma=<?= $id ?>" method="post" enctype="multipart/form-data">
    <main class="container admin" id="content">
        <h1>Editar Plataforma</h1>
        <?php if ($errores['warning']) { ?>
            <div class="alert alert-danger"><?= $errores['warning'] ?></div>
        <?php } ?>

        <div class="admin-article">
            <section class="image">
                <?php if ($saved_image) { ?>
                    <label>Logo actual:</label>
                    <img src="../uploads/<?= html_escape($plataforma['picture']) ?>"
                        alt="Logo de <?= html_escape($plataforma['nombre']) ?>">

                    <div class="form-group">
                        <label for="image">Cambiar logo:</label>
                        <input type="file" name="image" class="form-control-file" id="image"><br>
                    </div>
                <?php } else { ?>
                    <label for="image">Subir Logo:</label>
                    <div class="form-group image-placeholder">
                        <input type="file" name="image" class="form-control-file" id="image"><br>
                    </div>
                <?php } ?>
            </section>

            <section class="text">
                <div class="form-group">
                    <label for="nombre">Nombre de la Plataforma: </label>
                    <input type="text" name="nombre" id="nombre" value="<?= html_escape($plataforma['nombre']) ?>"
                        class="form-control">
                </div>
                <div class="form-group">
                    <label for="precio_mensual">Precio Mensual: </label>
                    <input type="number" step="0.01" name="precio_mensual" id="precio_mensual"
                        value="<?= html_escape($plataforma['precio_mensual']) ?>"
                        class="form-control">
                </div>
                <div class="form-group">
                    <label for="pais_origen">País de Origen: </label>
                    <input type="text" name="pais_origen" id="pais_origen"
                        value="<?= html_escape($plataforma['pais_origen']) ?>"
                        class="form-control">
                </div>
                <div class="form-group">
                    <label for="anio_lanzamiento">Año de Lanzamiento: </label>
                    <input type="number" name="anio_lanzamiento" id="anio_lanzamiento"
                        value="<?= html_escape($plataforma['anio_lanzamiento']) ?>"
                        class="form-control">
                </div>
                <div class="form-group">
                    <label for="usuarios_activos">Usuarios Activos: </label>
                    <input type="number" name="usuarios_activos" id="usuarios_activos"
                        value="<?= html_escape($plataforma['usuarios_activos']) ?>"
                        class="form-control">
                </div>
                <input type="submit" name="actualizar" value="Guardar" class="btn btn-primary">
            </section>
        </div>
    </main>
</form>
<?php include '../includes/admin-footer.php'; ?>