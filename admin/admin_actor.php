<?php

declare(strict_types=1);
include '../includes/database-connection.php';
include '../includes/functions.php';
include '../includes/validate.php';
include '../models/Actor.php'; // Incluir la nueva clase

$uploads = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
$id = filter_input(INPUT_GET, 'id_actor', FILTER_VALIDATE_INT);

// Crear un nuevo objeto Actor
$actor_obj = new Actor($pdo, $uploads);

// Si estamos editando, cargar el actor existente
if ($id) {
    if (!$actor_obj->cargar($id)) {
        redirect('listar_actores.php', ['failure' => 'Actor no encontrado']);
    }
}

// Obtener datos del actor como array para el formulario
$actor = $actor_obj->aArray();
$saved_picture = $actor['picture'] ? true : false;

$errores = [
    'warning' => '',
    'nombre' => '',
    'apellido' => '',
    'fecha_nacimiento' => '',
    'nacionalidad' => '',
    'genero' => '',
    'plataforma' => '',
    'picture' => '',
    'fecha_debut' => '',
    'estado_actividad' => '',
    'especialidad' => '',
];

// Obtener lista de plataformas para el formulario
$sql = "SELECT id_plataforma, nombre FROM plataforma;";
$plataformas = pdo($pdo, $sql)->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Establecer propiedades del actor
        $actor_obj->setNombre($_POST['nombre'])
            ->setApellido($_POST['apellido'])
            ->setFechaNacimiento($_POST['fecha_nacimiento'])
            ->setNacionalidad($_POST['nacionalidad'])
            ->setGenero($_POST['genero']);

        // Establecer los nuevos campos
        $actor_obj->setFechaDebut($_POST['fecha_debut'] ?: null)
            ->setEstadoActividad($_POST['estado_actividad'] ?: null)
            ->setEspecialidad($_POST['especialidad'] ?: null);

        // Manejar carga de imagen
        if (isset($_FILES['picture']) && $_FILES['picture']['tmp_name'] && $_FILES['picture']['error'] === 0) {
            $actor_obj->subirImagen($_FILES['picture']);
        }

        // Guardar el actor
        $actor_obj->guardar();
        redirect('listar_actores.php', ['success' => 'Actor guardado']);
    } catch (InvalidArgumentException $e) {
        if ($e->getMessage() === "El nombre del actor es obligatorio") {
            $errores['nombre'] = $e->getMessage();
        } else {
            $errores['warning'] = $e->getMessage();
        }
        // Actualizar el array de actor para mostrar los valores enviados
        $actor['nombre'] = $_POST['nombre'] ?? '';
        $actor['apellido'] = $_POST['apellido'] ?? '';
        $actor['fecha_nacimiento'] = $_POST['fecha_nacimiento'] ?? '';
        $actor['nacionalidad'] = $_POST['nacionalidad'] ?? '';
        $actor['genero'] = $_POST['genero'] ?? '';
        $actor['id_plataforma'] = (int)($_POST['id_plataforma'] ?? 0);
        $actor['fecha_debut'] = $_POST['fecha_debut'] ?: null;
        $actor['estado_actividad'] = $_POST['estado_actividad'] ?: null;
        $actor['especialidad'] = $_POST['especialidad'] ?: null;
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

<form action="admin_actor.php?id_actor=<?= $id ?>" method="post" enctype="multipart/form-data">
    <main class="container admin" id="content">
        <h1><?= $id ? 'Editar' : 'Añadir' ?> Actor</h1>
        <?php if ($errores['warning']) { ?>
            <div class="alert alert-danger"><?= $errores['warning'] ?></div>
        <?php } ?>

        <div class="admin-article">
            <section class="image">
                <?php if ($saved_picture) { ?>
                    <label>Foto actual:</label>
                    <img src="../uploads/<?= html_escape($actor['picture']) ?>"
                        alt="<?= html_escape($actor['nombre'] . ' ' . $actor['apellido']) ?>">

                    <div class="form-group">
                        <label for="picture">Cambiar foto:</label>
                        <input type="file" name="picture" class="form-control-file" id="picture"><br>
                    </div>
                <?php } else { ?>
                    <label for="picture">Subir Foto:</label>
                    <div class="form-group image-placeholder">
                        <input type="file" name="picture" class="form-control-file" id="picture"><br>
                    </div>
                <?php } ?>
            </section>

            <section class="text">
                <div class="form-group">
                    <label for="nombre">Nombre: </label>
                    <input type="text" name="nombre" id="nombre" value="<?= html_escape($actor['nombre']) ?>"
                        class="form-control <?= $errores['nombre'] ? 'is-invalid' : '' ?>">
                    <?php if ($errores['nombre']) { ?>
                        <div class="invalid-feedback"><?= $errores['nombre'] ?></div>
                    <?php } ?>
                </div>
                <div class="form-group">
                    <label for="apellido">Apellido: </label>
                    <input type="text" name="apellido" id="apellido" value="<?= html_escape($actor['apellido']) ?>" class="form-control">
                </div>
                <div class="form-group">
                    <label for="fecha_nacimiento">Fecha de nacimiento: </label>
                    <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="<?= html_escape($actor['fecha_nacimiento']) ?>" class="form-control">
                </div>

                <!-- Nuevos campos -->
                <div class="form-group">
                    <label for="fecha_debut">Fecha de debut: </label>
                    <input type="date" name="fecha_debut" id="fecha_debut" value="<?= html_escape($actor['fecha_debut'] ?? '') ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label for="estado_actividad">Estado de actividad: </label>
                    <select name="estado_actividad" id="estado_actividad" class="form-control">
                        <option value="">Seleccionar estado</option>
                        <option value="Activo" <?= ($actor['estado_actividad'] == 'Activo') ? 'selected' : '' ?>>Activo</option>
                        <option value="Retirado" <?= ($actor['estado_actividad'] == 'Retirado') ? 'selected' : '' ?>>Retirado</option>
                        <option value="En pausa" <?= ($actor['estado_actividad'] == 'En pausa') ? 'selected' : '' ?>>En pausa</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="especialidad">Especialidad: </label>
                    <select name="especialidad" id="especialidad" class="form-control">
                        <option value="">Seleccionar especialidad</option>
                        <option value="Drama" <?= ($actor['especialidad'] == 'Drama') ? 'selected' : '' ?>>Drama</option>
                        <option value="Comedia" <?= ($actor['especialidad'] == 'Comedia') ? 'selected' : '' ?>>Comedia</option>
                        <option value="Acción" <?= ($actor['especialidad'] == 'Acción') ? 'selected' : '' ?>>Acción</option>
                        <option value="Terror" <?= ($actor['especialidad'] == 'Terror') ? 'selected' : '' ?>>Terror</option>
                        <option value="Ciencia Ficción" <?= ($actor['especialidad'] == 'Ciencia Ficción') ? 'selected' : '' ?>>Ciencia Ficción</option>
                        <option value="Romance" <?= ($actor['especialidad'] == 'Romance') ? 'selected' : '' ?>>Romance</option>
                        <option value="Animación" <?= ($actor['especialidad'] == 'Animación') ? 'selected' : '' ?>>Animación</option>
                        <option value="Documental" <?= ($actor['especialidad'] == 'Documental') ? 'selected' : '' ?>>Documental</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="nacionalidad">Nacionalidad: </label>
                    <input type="text" name="nacionalidad" id="nacionalidad" value="<?= html_escape($actor['nacionalidad']) ?>" class="form-control">
                </div>
                <div class="form-group">
                    <label for="genero">Género: </label>
                    <input type="text" name="genero" id="genero" value="<?= html_escape($actor['genero']) ?>" class="form-control">
                </div>
                <div class="form-actions">
                    <input type="submit" name="actualizar" value="Guardar" class="btn btn-primary">
                    <a href="listar_actores.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </section>
        </div>
    </main>
</form>
<?php include '../includes/admin-footer.php'; ?>