
<?php
// ==================== ARCHIVO: api/salones.php =================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    if (isset($_GET['action']) && $_GET['action'] == 'disponibles') {
        getSalonesDisponibles($db);
    } else {
        getAllSalones($db);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function getAllSalones($db) {
    try {
        $query = "SELECT s.id, s.nombre, s.descripcion, s.precio, s.capacidad_maxima, s.horario,
                         c.nombre as curso_nombre, c.descripcion as curso_descripcion,
                         COUNT(i.id) as inscripciones_actuales,
                         (s.capacidad_maxima - COUNT(i.id)) as cupos_disponibles
                  FROM salones s
                  INNER JOIN cursos c ON s.curso_id = c.id
                  LEFT JOIN inscripciones i ON s.id = i.salon_id AND i.estado IN ('Activa', 'Pendiente') AND i.deleted_at IS NULL
                  WHERE s.deleted_at IS NULL AND s.estado = 'Activo'
                  GROUP BY s.id
                  ORDER BY c.nombre, s.nombre";
                  
        $stmt = $db->prepare($query);
        $stmt->execute();
        $salones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $salones]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getSalonesDisponibles($db) {
    try {
        $query = "SELECT s.id, s.nombre, s.descripcion, s.precio, s.capacidad_maxima, s.horario,
                         c.nombre as curso_nombre, c.descripcion as curso_descripcion,
                         COUNT(i.id) as inscripciones_actuales,
                         (s.capacidad_maxima - COUNT(i.id)) as cupos_disponibles
                  FROM salones s
                  INNER JOIN cursos c ON s.curso_id = c.id
                  LEFT JOIN inscripciones i ON s.id = i.salon_id AND i.estado IN ('Activa', 'Pendiente') AND i.deleted_at IS NULL
                  WHERE s.deleted_at IS NULL AND s.estado = 'Activo'
                  GROUP BY s.id
                  HAVING cupos_disponibles > 0
                  ORDER BY c.nombre, s.nombre";
                  
        $stmt = $db->prepare($query);
        $stmt->execute();
        $salones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $salones]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
