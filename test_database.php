<?php
// Archivo de prueba para verificar la conexión a la base de datos
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Prueba de Conexión a Base de Datos</h2>";

try {
    // Cargar configuración
    $config = require_once 'server/config.php';
    
    echo "<p>✅ Configuración cargada correctamente</p>";
    echo "<p><strong>Host:</strong> " . $config['db']['host'] . "</p>";
    echo "<p><strong>Base de datos:</strong> " . $config['db']['dbname'] . "</p>";
    echo "<p><strong>Usuario:</strong> " . $config['db']['user'] . "</p>";
    
    // Intentar conexión
    $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Conexión a la base de datos exitosa</p>";
    
    // Verificar tablas
    $tablas = ['usuarios', 'cursos', 'salones', 'inscripciones', 'pagos'];
    echo "<h3>📋 Verificando tablas:</h3>";
    
    foreach ($tablas as $tabla) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ Tabla <strong>$tabla</strong> existe</p>";
            
            // Contar registros
            $count = $pdo->query("SELECT COUNT(*) FROM $tabla")->fetchColumn();
            echo "<p>&nbsp;&nbsp;&nbsp;📊 Registros: $count</p>";
        } else {
            echo "<p>❌ Tabla <strong>$tabla</strong> NO existe</p>";
        }
    }
    
    // Verificar usuario admin
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'admin'");
    $adminCount = $stmt->fetchColumn();
    echo "<h3>👤 Usuario Administrador:</h3>";
    echo "<p>✅ Administradores encontrados: $adminCount</p>";
    
    if ($adminCount > 0) {
        $stmt = $pdo->query("SELECT email FROM usuarios WHERE rol = 'admin' LIMIT 1");
        $adminEmail = $stmt->fetchColumn();
        echo "<p><strong>Email admin:</strong> $adminEmail</p>";
        echo "<p><strong>Contraseña:</strong> admin123</p>";
    }
    
    echo "<h3>🎉 ¡Todo listo!</h3>";
    echo "<p>La base de datos está configurada correctamente.</p>";
    echo "<p><a href='templates/admin/inscripciones.php' target='_blank'>Ir a Gestión de Inscripciones</a></p>";
    
} catch (PDOException $e) {
    echo "<p>❌ Error de conexión: " . $e->getMessage() . "</p>";
    echo "<h3>🔧 Soluciones:</h3>";
    echo "<ul>";
    echo "<li>Verifica que MySQL esté ejecutándose</li>";
    echo "<li>Verifica que la base de datos 'ourcenter' exista</li>";
    echo "<li>Verifica las credenciales en server/config.php</li>";
    echo "<li>Importa el archivo database/inscripciones_tables.sql</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #0a1b5c; }
h3 { color: #1a3ba0; margin-top: 20px; }
p { margin: 5px 0; }
a { color: #0a1b5c; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
