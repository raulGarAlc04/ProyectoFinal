<?php
$type     = 'mysql';
$server   = 'localhost';
$db       = 'content_world';
$charset  = 'utf8mb4';

$username = 'content_user';
$password = 'user1234';

$options  = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false
];

$dsn = "$type:host=$server;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), $e->getCode());
}
?>