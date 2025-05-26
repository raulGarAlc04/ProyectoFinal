<?php
declare(strict_types = 1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require './includes/database-connection.php';
require './includes/functions.php';

session_start();

if (isset($_GET['user_id'])) {
    // Actualizar el campo activo a 0
    $sql = "UPDATE usuarios SET activo = 0 WHERE id = :id";
    try {
        pdo($pdo, $sql, [':id' => $_GET['user_id']]);
    } catch (PDOException $e) {
        // Manejar el error si ocurre
        error_log("Error al actualizar estado activo: " . $e->getMessage());
    }
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la sesión completamente, borrar también la cookie de sesión
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destruir la sesión
session_destroy();

// Redirigir al index
header("Location: index.php");
exit;
?>