<?php
// Configuración de headers para AJAX
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir archivos de configuración
require_once '../../../config/db.php';
require_once '../../../config/init.php';

try {
    // Verificar si el usuario está autenticado como administrador
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        throw new Exception('Acceso no autorizado');
    }

    $response = [
        'estudiantes' => [],
        'cursos' => [],
        'success' => true
    ];

    // Cargar estudiantes
    $stmt = $pdo->prepare("
        SELECT id, nombre, apellido, email, telefono, documento 
        FROM usuarios 
        WHERE rol = 'estudiante' 
        ORDER BY nombre, apellido
    ");
    $stmt->execute();
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($estudiantes as $estudiante) {
        $response['estudiantes'][] = [
            'id' => $estudiante['id'],
            'nombre' => $estudiante['nombre'] . ' ' . $estudiante['apellido'],
            'email' => $estudiante['email'],
            'telefono' => $estudiante['telefono'] ?: 'N/A',
            'documento' => $estudiante['documento'] ?: 'N/A'
        ];
    }

    // Cargar cursos
    $stmt = $pdo->prepare("
        SELECT c.id, c.nombre, c.descripcion, c.precio, c.duracion, 
               s.nombre as salon_nombre, p.nombre as profesor_nombre
        FROM cursos c
        LEFT JOIN salones s ON c.salon_id = s.id
        LEFT JOIN usuarios p ON c.profesor_id = p.id
        WHERE c.estado = 'activo'
        ORDER BY c.nombre
    ");
    $stmt->execute();
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cursos as $curso) {
        $response['cursos'][$curso['id']] = [
            'nombre' => $curso['nombre'],
            'descripcion' => $curso['descripcion'],
            'precio' => (float)$curso['precio'],
            'duracion' => $curso['duracion'] . ' semanas',
            'salon' => $curso['salon_nombre'] ?: 'Por asignar',
            'profesor' => $curso['profesor_nombre'] ?: 'Por asignar'
        ];
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'estudiantes' => [],
        'cursos' => []
    ]);
}
?>