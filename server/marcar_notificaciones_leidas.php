<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] !== 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

try {
    $usuario_id = $_SESSION['usuario_id'];
    
    $stmt = $pdo->prepare("UPDATE notificaciones SET leida = 1, fecha_lectura = NOW() WHERE usuario_destino_id = ? AND leida = 0");
    $stmt->execute([$usuario_id]);
    
    echo json_encode(['success' => true, 'message' => 'Notificaciones marcadas como leídas']);
} catch (PDOException $e) {
    error_log("Error al marcar notificaciones: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>