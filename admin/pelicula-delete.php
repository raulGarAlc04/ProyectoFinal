<?php
require '../includes/database-connection.php';
require '../includes/functions.php';
require '../models/Pelicula.php'; // Incluir la nueva clase

$id = filter_input(INPUT_GET, 'id_pelicula', FILTER_VALIDATE_INT);
if (!$id) {
    redirect('listar_peliculas.php', ['failure' => 'Pelicula no encontrada']);
}

// Crear un nuevo objeto Pelicula y cargarlo
$pelicula_obj = new Pelicula($pdo);
if (!$pelicula_obj->cargar($id)) {
    redirect('listar_peliculas.php', ['failure' => 'Pelicula no encontrada']);
}

// Obtener datos de la película
$pelicula = $pelicula_obj->aArray();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Eliminar la película
        $pelicula_obj->eliminar();
        redirect('listar_peliculas.php', ['success' => 'Pelicula eliminada']);
    } catch (PDOException $e) {
        throw $e;
    }
}

include '../includes/admin-header.php';
?>

<main class="container admin" id="content">
    <form action="pelicula-delete.php?id_pelicula=<?= $id ?>" method="post" class="narrow">
    <h1>Eliminar Pelicula</h1>
    <p>Haga click para confirmar el borrado de la pelicula: <em><?= html_escape($pelicula['nombre']) ?></em></p>
    <input type="submit" name="delete" value="Confirmar" class="btn btn-primary">
    <a href="listar_peliculas.php" class="btn btn-danger">Cancelar</a>
    </form>
</main>

<?php include '../includes/admin-footer.php'; ?>