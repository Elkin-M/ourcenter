<?php
// Archivo: test_conexion_pdo.php
// Script para probar la conexión a la base de datos utilizando PDO

// Configuración de la base de datos
$host = 'localhost';      // Servidor de la base de datos
$dbname = 'bill_bot';     // Nombre de la base de datos
$username = 'root';       // Usuario de la base de datos
$password = '';           // Contraseña del usuario (vacía por defecto en XAMPP)
$charset = 'utf8mb4';     // Conjunto de caracteres recomendado

// Mensaje de resultado inicial
$mensaje = "";
$conexion_exitosa = false;

try {
    // Construir el DSN (Data Source Name)
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    
    // Opciones de conexión
    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,           // Lanzar excepciones en caso de error
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      // Obtener resultados como arrays asociativos
        PDO::ATTR_EMULATE_PREPARES => false,                   // Usar preparación nativa
    ];
    
    // Intentar la conexión
    $pdo = new PDO($dsn, $username, $password, $opciones);
    
    // Si llegamos aquí, la conexión fue exitosa
    $mensaje = "¡Conexión exitosa a la base de datos '$dbname'!";
    $conexion_exitosa = true;
    
    // Verificar la existencia de las tablas
    $tablas = ['administrador', 'facturadores'];
    $informacion_tablas = [];
    
    foreach ($tablas as $tabla) {
        $query = $pdo->query("SHOW TABLES LIKE '$tabla'");
        $existe = $query->rowCount() > 0;
        
        if ($existe) {
            // Obtener estructura de la tabla
            $estructura = $pdo->query("DESCRIBE $tabla")->fetchAll();
            $columnas = [];
            
            foreach ($estructura as $columna) {
                $columnas[] = $columna['Field'] . ' (' . $columna['Type'] . ')';
            }
            
            $informacion_tablas[$tabla] = [
                'existe' => true,
                'columnas' => $columnas,
                'num_filas' => $pdo->query("SELECT COUNT(*) FROM $tabla")->fetchColumn()
            ];
        } else {
            $informacion_tablas[$tabla] = [
                'existe' => false
            ];
        }
    }
    
} catch (PDOException $e) {
    // En caso de error de conexión
    $mensaje = "Error de conexión: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Conexión PDO</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        h1 {
            color: #2c3e50;
            text-align: center;
        }
        .success {
            color: #27ae60;
            font-weight: bold;
        }
        .error {
            color: #e74c3c;
            font-weight: bold;
        }
        .table-info {
            margin-top: 20px;
            background-color: #fff;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .table-name {
            font-weight: bold;
            color: #3498db;
            margin-bottom: 10px;
        }
        ul {
            list-style-type: none;
            padding-left: 20px;
        }
        li {
            margin-bottom: 5px;
        }
        .not-found {
            color: #e74c3c;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Prueba de Conexión a la Base de Datos con PDO</h1>
        
        <div class="<?php echo $conexion_exitosa ? 'success' : 'error'; ?>">
            <?php echo $mensaje; ?>
        </div>
        
        <?php if ($conexion_exitosa): ?>
            <h2>Información de la base de datos</h2>
            <p><strong>Host:</strong> <?php echo $host; ?></p>
            <p><strong>Base de datos:</strong> <?php echo $dbname; ?></p>
            <p><strong>Conjunto de caracteres:</strong> <?php echo $charset; ?></p>
            
            <h2>Información de tablas</h2>
            <?php foreach ($informacion_tablas as $nombre_tabla => $info): ?>
                <div class="table-info">
                    <div class="table-name">Tabla: <?php echo $nombre_tabla; ?></div>
                    
                    <?php if ($info['existe']): ?>
                        <p><strong>Estado:</strong> <span class="success">Existe</span></p>
                        <p><strong>Número de registros:</strong> <?php echo $info['num_filas']; ?></p>
                        
                        <p><strong>Columnas:</strong></p>
                        <ul>
                            <?php foreach ($info['columnas'] as $columna): ?>
                                <li><?php echo $columna; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p><strong>Estado:</strong> <span class="not-found">No encontrada</span></p>
                        <p>Esta tabla no existe en la base de datos.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <p style="margin-top: 20px; text-align: center;">
                <small>Este script proporciona información básica sobre la conexión y la estructura de la base de datos.</small>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>