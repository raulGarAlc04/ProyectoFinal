<?php

declare(strict_types=1);
require '../includes/database-connection.php';
require '../includes/functions.php';
require '../models/Actor.php'; // Incluir la nueva clase

$id = filter_input(INPUT_GET, 'id_actor', FILTER_VALIDATE_INT);
if (!$id) {
    redirect('listar_actores.php', ['failure' => 'Actor no encontrado']);
}

// Crear un nuevo objeto Actor y cargarlo
$actor_obj = new Actor($pdo);
if (!$actor_obj->cargar($id)) {
    redirect('listar_actores.php', ['failure' => 'Actor no encontrado']);
}

// Obtener datos del actor
$actor = $actor_obj->aArray();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Eliminar el actor
        $actor_obj->eliminar();
        redirect('listar_actores.php', ['success' => 'Actor eliminado']);
    } catch (Exception $e) {
        throw $e;
    }
}
?>
<?php include '../includes/admin-header.php'; ?>
<main class="container admin" id="content">
    <form action="actor-delete.php?id_actor=<?= $id ?>" method="post" class="narrow">
        <h1>Eliminar Actor</h1>
        <p>Haga click para confirmar el borrado del actor: <em><?= html_escape($actor['nombre'] . ' ' . $actor['apellido']) ?></em></p>
        <input type="submit" name="delete" value="Confirmar" class="btn btn-primary">
        <a href="listar_actores.php" class="btn btn-danger">Cancelar</a>
    </form>
</main>
<?php include '../includes/admin-footer.php'; ?>