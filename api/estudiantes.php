<?php
// ==================== ARCHIVO: api/estudiantes.php ====================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            getEstudiantes($db);
            break;
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            createEstudiante($db, $input);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function getEstudiantes($db) {
    try {
        $search = $_GET['search'] ?? '';
        
        $query = "SELECT id, nombre, apellido, email, telefono, documento_identidad,
                         CONCAT(nombre, ' ', apellido) as nombre_completo
                  FROM estudiantes 
                  WHERE deleted_at IS NULL";
        
        $params = [];
        
        if (!empty($search)) {
            $query .= " AND (nombre LIKE ? OR apellido LIKE ? OR email LIKE ? OR documento_identidad LIKE ?)";
            $search_param = "%$search%";
            $params = [$search_param, $search_param, $search_param, $search_param];
        }
        
        $query .= " ORDER BY nombre, apellido";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $estudiantes]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function createEstudiante($db, $data) {
    try {
        // Validar email único
        $check_query = "SELECT id FROM estudiantes WHERE email = ? AND deleted_at IS NULL";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$data['email']]);
        
        if ($check_stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'El email ya está registrado']);
            return;
        }
        
        $query = "INSERT INTO estudiantes (nombre, apellido, email, telefono, documento_identidad, created_at) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $data['nombre'],
            $data['apellido'],
            $data['email'],
            $data['telefono'] ?? null,
            $data['documento'] ?? null
        ]);
        
        $estudiante_id = $db->lastInsertId();
        
        // Retornar el estudiante creado
        $query_get = "SELECT id, nombre, apellido, email, telefono, documento_identidad,
                             CONCAT(nombre, ' ', apellido) as nombre_completo
                      FROM estudiantes WHERE id = ?";
        $stmt_get = $db->prepare($query_get);
        $stmt_get->execute([$estudiante_id]);
        $estudiante = $stmt_get->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'message' => 'Estudiante creado exitosamente', 'data' => $estudiante]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>