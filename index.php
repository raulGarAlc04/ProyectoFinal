<?php
declare(strict_types = 1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require './includes/database-connection.php';
require './includes/functions.php';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    // Validar que los campos no estén vacíos
    if (empty($email) || empty($password)) {
        $error = "Por favor, completa todos los campos.";
    } else {
        // Consultar la base de datos para verificar el usuario (sin filtrar por activo)
        $sql = "SELECT id, email, password, nombre, es_admin, foto_perfil FROM usuarios WHERE email = :email";
        $user = pdo($pdo, $sql, [':email' => $email])->fetch(PDO::FETCH_ASSOC);
        
        // Verificar si el usuario existe y la contraseña es correcta
        if ($user && $password === $user['password']) { // En producción usar password_verify()
            // Actualizar el campo activo a 1
            $update_sql = "UPDATE usuarios SET activo = 1 WHERE id = :id";
            pdo($pdo, $update_sql, [':id' => $user['id']]);
            
            // Iniciar sesión
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['foto_perfil'] = $user['foto_perfil'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['is_admin'] = $user['es_admin'];
            
            // Redirigir según el rol del usuario, pasando el ID por la URL
            if ($user['es_admin']) {
                header("Location: ./admin/index.php?user_id=" . $user['id']);
            } else {
                header("Location: ./user/index.php?user_id=" . $user['id']);
            }
            exit;
        } else {
            $error = "Email o contraseña incorrectos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="./css/login_styles.css">
    <title>LOGIN</title>
</head>
<body>
    <div class="login-container">
        <h2>Bienvenido/a</h2>
        <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Iniciar Sesión</button>
        </form>
        <div class="links">
            <a href="register.php">¿No tienes cuenta? Crea una</a>
        </div>
    </div>
</body>
</html>