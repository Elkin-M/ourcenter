<?php
// Archivo de prueba para diagnosticar problemas
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'Conexión de prueba exitosa',
    'php_version' => PHP_VERSION,
    'time' => date('Y-m-d H:i:s')
]);
?>
