<?php
$host = 'localhost';
$dbname = 'ourcenter';
$user = 'root';
$pass = '';


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Encabezado
    echo "-- Estructura de la base de datos `$dbname`\n\n";

    // Obtener todas las tablas
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        // Obtener la sentencia CREATE TABLE
        $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $createTable = $row['Create Table'] ?? $row[1];

        echo "-- --------------------------------------------------\n";
        echo "-- Estructura para la tabla `$table`\n";
        echo "DROP TABLE IF EXISTS `$table`;\n";
        echo $createTable . ";\n\n";
    }
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
