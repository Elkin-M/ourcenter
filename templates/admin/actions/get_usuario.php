<?php
// Configurar cabeceras
header('Content-Type: application/json');

// Verificar si se pasó un ID por GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de usuario no proporcionado']);
    exit;
}

$id = intval($_GET['id']);

// Conexión a la base de datos (ajusta estos valores según tu configuración)
$host = 'localhost';
$db = 'ourcenter'; // cambia a tu base si es diferente
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Consulta para obtener información completa del usuario
    $stmt = $pdo->prepare("
        SELECT u.*, r.nombre AS rol_nombre
        FROM usuarios u
        LEFT JOIN roles r ON u.rol_id = r.id
        WHERE u.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
    }

    echo json_encode($usuario);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    exit;
}
