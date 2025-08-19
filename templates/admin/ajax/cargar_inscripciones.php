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

    // Obtener parámetros de filtrado
    $estado = $_GET['estado'] ?? '';
    $pago = $_GET['pago'] ?? '';
    $busqueda = $_GET['busqueda'] ?? '';

    // Construir consulta base
    $sql = "
        SELECT 
            i.id,
            i.fecha_inscripcion,
            i.estado,
            i.metodo_inscripcion,
            i.notas,
            i.created_at,
            i.updated_at,
            u.id as estudiante_id,
            u.nombre as estudiante_nombre,
            u.apellido as estudiante_apellido,
            u.email as estudiante_email,
            u.telefono as estudiante_telefono,
            c.id as curso_id,
            c.nombre as curso_nombre,
            c.precio as curso_precio,
            s.nombre as salon_nombre,
            p.estado as estado_pago,
            p.monto as monto_pago
        FROM inscripciones i
        INNER JOIN usuarios u ON i.estudiante_id = u.id
        INNER JOIN cursos c ON i.curso_id = c.id
        LEFT JOIN salones s ON c.salon_id = s.id
        LEFT JOIN pagos p ON i.id = p.inscripcion_id
        WHERE 1=1
    ";

    $params = [];

    // Aplicar filtros
    if ($estado) {
        $sql .= " AND i.estado = ?";
        $params[] = $estado;
    }

    if ($pago) {
        if ($pago === 'null') {
            $sql .= " AND p.estado IS NULL";
        } else {
            $sql .= " AND p.estado = ?";
            $params[] = $pago;
        }
    }

    if ($busqueda) {
        $sql .= " AND (u.nombre LIKE ? OR u.apellido LIKE ? OR u.email LIKE ? OR c.nombre LIKE ?)";
        $busquedaParam = "%$busqueda%";
        $params[] = $busquedaParam;
        $params[] = $busquedaParam;
        $params[] = $busquedaParam;
        $params[] = $busquedaParam;
    }

    $sql .= " ORDER BY i.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear datos para DataTables
    $data = [];
    foreach ($inscripciones as $inscripcion) {
        $data[] = [
            'id' => $inscripcion['id'],
            'estudiante' => [
                'id' => $inscripcion['estudiante_id'],
                'nombre' => $inscripcion['estudiante_nombre'] . ' ' . $inscripcion['estudiante_apellido'],
                'email' => $inscripcion['estudiante_email'],
                'telefono' => $inscripcion['estudiante_telefono']
            ],
            'curso' => [
                'id' => $inscripcion['curso_id'],
                'nombre' => $inscripcion['curso_nombre'],
                'precio' => $inscripcion['curso_precio'],
                'salon' => $inscripcion['salon_nombre'] ?: 'Por asignar'
            ],
            'fecha_inscripcion' => date('d/m/Y', strtotime($inscripcion['fecha_inscripcion'])),
            'estado' => $inscripcion['estado'],
            'estado_pago' => $inscripcion['estado_pago'] ?: 'Sin pago',
            'monto_pago' => $inscripcion['monto_pago'] ?: 0,
            'metodo_inscripcion' => $inscripcion['metodo_inscripcion'],
            'notas' => $inscripcion['notas'],
            'created_at' => $inscripcion['created_at'],
            'updated_at' => $inscripcion['updated_at']
        ];
    }

    // Obtener estadísticas
    $stats = obtenerEstadisticas($pdo);

    echo json_encode([
        'success' => true,
        'data' => $data,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => [],
        'stats' => []
    ]);
}

function obtenerEstadisticas($pdo) {
    // Total inscripciones
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM inscripciones");
    $total = $stmt->fetch()['total'];

    // Inscripciones activas
    $stmt = $pdo->query("SELECT COUNT(*) as activas FROM inscripciones WHERE estado = 'Activa'");
    $activas = $stmt->fetch()['activas'];

    // Inscripciones pendientes
    $stmt = $pdo->query("SELECT COUNT(*) as pendientes FROM inscripciones WHERE estado = 'Pendiente'");
    $pendientes = $stmt->fetch()['pendientes'];

    // Pagos pendientes
    $stmt = $pdo->query("SELECT COUNT(*) as pagos_pendientes FROM pagos WHERE estado = 'Pendiente'");
    $pagosPendientes = $stmt->fetch()['pagos_pendientes'];

    return [
        'total' => $total,
        'activas' => $activas,
        'pendientes' => $pendientes,
        'pagos_pendientes' => $pagosPendientes
    ];
}
?>
