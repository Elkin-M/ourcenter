<?php
/**
 * Configuración de conexión a la base de datos
 */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Cambia por tu usuario de MySQL
define('DB_PASS', '');            // Cambia por tu contraseña de MySQL
define('DB_NAME', 'ourcenter');   // Nombre de la base de datos
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

/**
 * Función para crear una conexión a la base de datos
 */
function conectarDB() {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Verificar conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    
    // Establecer conjunto de caracteres a utf8
    $conexion->set_charset("utf8");
    
    return $conexion;
}