<?php

declare(strict_types=1);
require '../includes/database-connection.php';
require '../includes/functions.php';
require '../models/Plataforma.php';

$id = filter_input(INPUT_GET, 'id_plataforma', FILTER_VALIDATE_INT);
if (!$id) {
    redirect('listar_plataformas.php', ['failure' => 'Plataforma no encontrada']);
}

// Crear un nuevo objeto Plataforma y cargarlo
$plataforma_obj = new Plataforma($pdo);
if (!$plataforma_obj->cargar($id)) {
    redirect('listar_plataformas.php', ['failure' => 'Plataforma no encontrada']);
}

// Obtener datos de la plataforma
$plataforma = $plataforma_obj->aArray();

// Verificar si hay películas o series asociadas a esta plataforma
$sql = "SELECT 
    (SELECT COUNT(*) FROM pelicula WHERE id_plataforma = :id_plataforma1) +
    (SELECT COUNT(*) FROM serie WHERE id_plataforma = :id_plataforma2) AS total";
$contenido_asociado = pdo($pdo, $sql, [
    'id_plataforma1' => $id,
    'id_plataforma2' => $id
])->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Eliminar la plataforma
        $plataforma_obj->eliminar();
        redirect('listar_plataformas.php', ['success' => 'Plataforma eliminada']);
    } catch (Exception $e) {
        if ($e->getCode() == 2) { // Código personalizado para error de contenido asociado
            redirect('listar_plataformas.php', ['failure' => $e->getMessage()]);
        } else {
            throw $e;
        }
    }
}
?>
<?php include '../includes/admin-header.php'; ?>
<main class="container admin" id="content">
    <form action="plataforma-delete.php?id_plataforma=<?= $id ?>" method="post" class="narrow">
        <h1>Eliminar Plataforma</h1>
        <p>Haga click para confirmar el borrado de la plataforma: <em><?= html_escape($plataforma['nombre']) ?></em></p>

        <?php if ($contenido_asociado['total'] > 0) { ?>
            <div class="alert alert-warning">
                <p><strong>Advertencia:</strong> Esta plataforma tiene <?= $contenido_asociado['total'] ?>
                    elementos de contenido asociados (películas y/o series).</p>
            </div>
        <?php } ?>

        <input type="submit" name="delete" value="Confirmar" class="btn btn-primary">
        <a href="listar_plataformas.php" class="btn btn-danger">Cancelar</a>
    </form>
</main>
<?php include '../includes/admin-footer.php'; ?>