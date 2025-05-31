<?php

declare(strict_types=1);
require '../includes/database-connection.php';
require '../includes/functions.php';
require '../models/Serie.php';

$id = filter_input(INPUT_GET, 'id_serie', FILTER_VALIDATE_INT);

if (!$id) {
    include './page-not-found.php';
    exit;
}

// Crear un objeto Serie y cargarlo
$serie_obj = new Serie($pdo);
if (!$serie_obj->cargar($id)) {
    include './page-not-found.php';
    exit;
}

// Obtener los datos de la serie
$serie = $serie_obj->aArray();

// Obtener la valoración media y el número de valoraciones
$valoracion_media = $serie_obj->obtenerValoracionMedia();
$num_valoraciones = $serie_obj->obtenerNumeroValoraciones();

// Obtener los comentarios recientes
$comentarios_recientes = $serie_obj->obtenerComentariosRecientes(3);

// Consulta de actores
$sql = "SELECT a.id_actor, a.nombre, a.apellido, a.picture
    FROM actor_serie asr
    JOIN actor a ON asr.id_actor = a.id_actor
    WHERE asr.id_serie = :id_serie";
$actores_array = pdo($pdo, $sql, ['id_serie' => $id])->fetchAll();

$sql = "SELECT id_plataforma, nombre FROM plataforma";
$navigation = pdo($pdo, $sql)->fetchAll();
$section = $serie['id_serie'];
$title = $serie['nombre'];
$description = 'Plataforma de contenido';

include '../includes/header.php';

// Después de incluir el header, obtenemos el ID del usuario
$id_usuario = $_SESSION['user_id'] ?? null;

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id_usuario) {
    if (isset($_POST['add_fav'])) {
        $valoracion = $_POST['valoracion'] ?? null;
        $comentario = $_POST['comentario'] ?? null;

        $sql = "INSERT INTO usuario_serie (id_usuario, id_serie, valoracion, comentario) 
        VALUES (:id_usuario, :id_serie, :valoracion, :comentario)
        ON DUPLICATE KEY UPDATE valoracion = VALUES(valoracion), comentario = VALUES(comentario)";
        try {
            pdo($pdo, $sql, [
                ':id_usuario' => $id_usuario,
                ':id_serie' => $id,
                ':valoracion' => $valoracion,
                ':comentario' => $comentario
            ]);
        } catch (Exception $e) {
            error_log("Error al añadir favorito: " . $e->getMessage());
        }
    } elseif (isset($_POST['remove_fav'])) {
        // Eliminar de favoritos
        $sql = "DELETE FROM usuario_serie WHERE id_usuario = :id_usuario AND id_serie = :id_serie";
        try {
            pdo($pdo, $sql, [
                ':id_usuario' => $id_usuario,
                ':id_serie' => $id
            ]);
        } catch (Exception $e) {
            error_log("Error al eliminar favorito: " . $e->getMessage());
        }
    }
    // Redirigir para evitar reenvío del formulario
    header("Location: " . $_SERVER['PHP_SELF'] . "?id_serie=" . $id);
    exit;
}

// Verificar si es favorita
$is_favorite = false;
$valoracion = null;
$comentario = null;
if ($id_usuario) {
    $sql = "SELECT valoracion, comentario FROM usuario_serie WHERE id_usuario = :id_usuario AND id_serie = :id_serie";
    $fav = pdo($pdo, $sql, [':id_usuario' => $id_usuario, ':id_serie' => $id])->fetch();
    if ($fav) {
        $is_favorite = true;
        $valoracion = $fav['valoracion'];
        $comentario = $fav['comentario'];
    }
}
?>

<main class="article container" id="content">
    <section class="image">
        <img src="../uploads/<?= html_escape($serie['image_file'] ?? 'blank.png') ?>"
            alt="<?= html_escape($serie['image_alt']) ?>">
    </section>
    <section class="text">
        <h1><?= html_escape($serie['nombre']) ?></h1>
        <div class="content">Año de estreno: <?= html_escape($serie['anio_estreno']) ?></div>
        <div class="content">Temporadas: <?= html_escape($serie['n_temporadas']) ?></div>

        <!-- Mostrar valoración media -->
        <div class="rating-summary">
            <?php if ($valoracion_media): ?>
                <div class="average-rating">
                    <span class="rating-value"><?= html_escape($valoracion_media) ?></span>/10
                    <span class="rating-count">(<?= $num_valoraciones ?> valoraciones)</span>
                </div>
            <?php else: ?>
                <div class="no-ratings">Sin valoraciones todavía</div>
            <?php endif; ?>
        </div>

        <?php if ($id_usuario): ?>
            <div class="favorite-section">
                <?php if ($is_favorite): ?>
                    <div class="favorite-status">
                        <p><i class="fas fa-heart"></i> Esta serie está en tus favoritos</p>
                    </div>

                    <div class="current-rating">
                        <?php if ($valoracion): ?>
                            <p><strong>Tu valoración:</strong> <?= html_escape($valoracion) ?>/10</p>
                        <?php endif; ?>
                        <?php if ($comentario): ?>
                            <p><strong>Tu comentario:</strong> <?= html_escape($comentario) ?></p>
                        <?php endif; ?>
                    </div>

                    <form method="post" class="rating-form">
                        <div class="form-group">
                            <label for="valoracion">Valoración:</label>
                            <select name="valoracion" id="valoracion">
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?= $i ?>" <?= $valoracion == $i ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="comentario">Comentario:</label>
                            <textarea name="comentario" id="comentario"><?= html_escape($comentario ?? '') ?></textarea>
                        </div>

                        <div class="button-group">
                            <button type="submit" name="add_fav" class="btn btn-primary">
                                <i class="fas fa-sync"></i> Actualizar valoración
                            </button>
                            <button type="submit" name="remove_fav" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Quitar de favoritos
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <form method="post">
                        <button type="submit" name="add_fav" class="btn btn-success">
                            <i class="fas fa-heart"></i> Añadir a favoritos
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <p class="credit">
            Disponible en <a href="ver_plataforma.php?id_plataforma=<?= $serie['id_plataforma'] ?>">
                <?= html_escape($serie['plataforma']) ?>
            </a>
        </p>

        <p class="credit">
            Reparto:
        <div class="actor-cards">
            <?php foreach ($actores_array as $actor) { ?>
                <a href="ver_actor.php?id_actor=<?= $actor['id_actor'] ?>" class="actor-card">
                    <img src="../uploads/<?= html_escape($actor['picture'] ?? 'blank.png') ?>"
                        alt="<?= html_escape($actor['nombre'] . ' ' . $actor['apellido']) ?>"
                        class="actor-thumb">
                    <span class="actor-name"><?= html_escape($actor['nombre'] . ' ' . $actor['apellido']) ?></span>
                </a>
            <?php } ?>
        </div>
        </p>

        <!-- Mostrar comentarios recientes -->
        <?php if (!empty($comentarios_recientes)): ?>
            <div class="recent-comments">
                <h3>Comentarios recientes</h3>
                <?php foreach ($comentarios_recientes as $comentario): ?>
                    <div class="comment">
                        <div class="comment-header">
                            <span class="comment-author">Usuario #<?= html_escape($comentario['id_usuario']) ?></span>
                            <span class="comment-rating"><?= html_escape($comentario['valoracion']) ?>/10</span>
                            <?php if (isset($comentario['fecha_valoracion'])): ?>
                                <span class="comment-date"><?= date('d/m/Y', strtotime($comentario['fecha_valoracion'])) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="comment-body">
                            <?= html_escape($comentario['comentario']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include '../includes/footer.php'; ?>