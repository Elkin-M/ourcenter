<?php
// procesar_registro.php

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "our_center";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener datos del formulario
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$tipo_documento = $_POST['tipo_documento'];
$documento_identidad = $_POST['documento_identidad'];
$fecha_nacimiento = $_POST['fecha_nacimiento'];
$email = $_POST['email'];
$telefono = $_POST['telefono'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);
$accept_terms = isset($_POST['accept_terms']) ? 1 : 0;

// Validar datos (ejemplo básico)
if (empty($nombre) || empty($apellido) || empty($tipo_documento) || empty($documento_identidad) || empty($fecha_nacimiento) || empty($email) || empty($telefono) || empty($password) || !$accept_terms) {
    echo "Todos los campos son obligatorios y debe aceptar los términos y condiciones.";
    exit;
}

// Preparar y ejecutar la consulta SQL
$sql = "INSERT INTO usuarios (nombre, apellido, tipo_documento, documento_identidad, fecha_nacimiento, email, telefono, password, accept_terms)
        VALUES ('$nombre', '$apellido', '$tipo_documento', '$documento_identidad', '$fecha_nacimiento', '$email', '$telefono', '$password', '$accept_terms')";

if ($conn->query($sql) === TRUE) {
    echo "Registro exitoso";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Cerrar conexión
$conn->close();
?>
