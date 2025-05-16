<?php
$config = require __DIR__ . '/../server/config.php';

$db = $config['db']; // Extraemos solo los datos de BD

$dsn = "mysql:host={$db['host']};dbname={$db['dbname']};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $db['user'], $db['password'], $options);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
