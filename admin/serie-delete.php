<?php
declare(strict_types=1);
require '../includes/database-connection.php';
require '../includes/functions.php';
require '../models/Serie.php'; // Incluir la nueva clase

$id = filter_input(INPUT_GET, 'id_serie', FILTER_VALIDATE_INT);
if (!$id) {
    redirect('listar_series.php', ['failure' => 'Serie no encontrada']);
}

// Crear un nuevo objeto Serie y cargarlo
$serie_obj = new Serie($pdo);
if (!$serie_obj->cargar($id)) {
    redirect('listar_series.php', ['failure' => 'Serie no encontrada']);
}

// Obtener datos de la serie
$serie = $serie_obj->aArray();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Eliminar la serie
        $serie_obj->eliminar();
        redirect('listar_series.php', ['success' => 'Serie eliminada']);
    } catch (PDOException $e) {
        throw $e;
    }
}
?>
<?php include '../includes/admin-header.php'; ?>
    <main class="container admin" id="content">
    <form action="serie-delete.php?id_serie=<?= $id ?>" method="post" class="narrow">
    <h1>Eliminar Serie</h1>
    <p>Haga click para confirmar el borrado de la serie: <em><?= html_escape($serie['nombre']) ?></em></p>
    <input type="submit" name="delete" value="Confirmar" class="btn btn-primary">
    <a href="listar_series.php" class="btn btn-danger">Cancelar</a>
    </form>
    </main>
<?php include '../includes/admin-footer.php'; ?>