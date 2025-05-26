<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>ERROR DE BASE DE DATOS</title>
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
</head>

<body style="padding: 3rem">
    <h1>No estás conectado a la base de datos</h1>
    <p>Estas son las posibles causas...</p>
    <?php
    switch ($e->getCode()) {
        case 0:
            echo '<p>El valor para <code>$type</code> debe ser <code>mysql</code>.<br>Es el primer valor en el DSN.</p>';
            break;
        case 2002:
            echo '<p>El valor para <code>$server</code> es incorrecto.<br>
                  Si estás usando MAMP o XAMPP prueba con <code>localhost</code>.<br>
                  Si sigues viendo el mismo error prueba con <code>127.0.0.1</code>. Esta dirección IP está reservada para tu máquina local.</p>';
            break;
        case 1044:
            echo '<p>Tu cuenta de usuario parece no tener permisos para trabajar con esta base de datos.<br> Verifica los permisos en phpMyAdmin.</p>';
            break;
        case 1045:
            echo '<p>El nombre de usuario o la contraseña parecen ser incorrectos.<br>En phpMyAdmin verifica que la cuenta de usuario fue creada.<br>Luego asegúrate de tener el nombre de usuario y contraseña correctos en las variables $username y $password.</p>';
            break;
        case 1049:
            echo '<p>El valor para <code>$db</code> es incorrecto.<br>Verifica el nombre de la base de datos en phpMyAdmin.</p>';
            break;
        case 1042:
            echo '<p>No se puede obtener el nombre del host para tu servidor de base de datos</p>';
            break;
        case 2002:
            echo '<p>No se puede conectar a MySQL en ___</p>';
            break;
        case 2003:
            echo '<p>1. Verifica que MySQL esté ejecutándose.<br>Si esto no funciona, verifica el número de puerto en phpMyAdmin. Ver <a href="http://notes.re/mysql/check-ports">http://notes.re/mysql/check-ports</a>.</p>';
            break;
        case 2005:
            echo '<p>El valor para $server es incorrecto.<br>
                  Si estás usando MAMP o XAMPP prueba con <code>localhost</code>.<br>
                  Si sigues viendo el mismo error prueba con <code>127.0.0.1</code>. Esta dirección IP está reservada para tu máquina local.</p>';
            break;
        case 2019:
            echo '<p>El <code>charset</code> en el DSN es incorrecto.<br>Configúralo como <code>utf8</code>.</p>';
            break;
        default:
            echo '<p>Primero, verifica que hayas: </p>
                 <ul>
                     <li>Creado la base de datos de ejemplo en phpMyAdmin</li>
                     <li>Creado una cuenta de usuario para acceder a la base de datos</li>
                 </ul>
                 <p>Luego, busca en la web el mensaje de error que has recibido abajo.</p>';
            break;
    }
    ?>
    <hr style="margin-top: 3rem;">
    <p>Aquí está el código de error SQL y el mensaje que creó el servidor web:</p>
    <p><b>Código de error SQLSTATE:</b> <?= $e->getCode() ?></p>
    <p><b>Mensaje de error: </b><?= $e->getMessage() ?></p>
    <?php exit; ?>
</body>
</html>