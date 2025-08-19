<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'stats':
                        getStats($db);
                        break;
                    case 'list':
                        getInscripciones($db);
                        break;
                    case 'details':
                        getInscripcionDetails($db, $_GET['id']);
                        break;
                    default:
                        getInscripciones($db);
                }
            } else {
                getInscripciones($db);
            }
            break;
            
        case 'POST':
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'create':
                        createInscripciones($db, $input);
                        break;
                    case 'update_status':
                        updateInscripcionStatus($db, $input);
                        break;
                }
            } else {
                createInscripciones($db, $input);
            }
            break;
            
        case 'PUT':
            updateInscripcion($db, $input);
            break;
            
        case 'DELETE':
            deleteInscripcion($db, $_GET['id']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function getStats($db) {
    try {
        $stats = [];
        
        // Total inscripciones
        $query = "SELECT COUNT(*) as total FROM inscripciones WHERE deleted_at IS NULL";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Inscripciones activas
        $query = "SELECT COUNT(*) as activas FROM inscripciones WHERE estado = 'Activa' AND deleted_at IS NULL";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stats['activas'] = $stmt->fetch(PDO::FETCH_ASSOC)['activas'];
        
        // Inscripciones pendientes
        $query = "SELECT COUNT(*) as pendientes FROM inscripciones WHERE estado = 'Pendiente' AND deleted_at IS NULL";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stats['pendientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['pendientes'];
        
        // Pagos pendientes
        $query = "SELECT COUNT(*) as pagos_pendientes FROM inscripciones i 
                 LEFT JOIN pagos p ON i.id = p.inscripcion_id 
                 WHERE (p.estado = 'Pendiente' OR p.id IS NULL) AND i.deleted_at IS NULL";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stats['pagos_pendientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['pagos_pendientes'];
        
        echo json_encode(['success' => true, 'data' => $stats]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getInscripciones($db) {
    try {
        $query = "SELECT i.id, i.estudiante_id, i.salon_id, i.fecha_inscripcion, i.estado, i.notas,
                         e.nombre as estudiante_nombre, e.apellido as estudiante_apellido, e.email as estudiante_email,
                         s.nombre as salon_nombre, s.descripcion as salon_descripcion, s.precio as salon_precio,
                         c.nombre as curso_nombre, c.descripcion as curso_descripcion,
                         p.estado as pago_estado, p.monto as pago_monto
                  FROM inscripciones i
                  INNER JOIN estudiantes e ON i.estudiante_id = e.id
                  INNER JOIN salones s ON i.salon_id = s.id
                  INNER JOIN cursos c ON s.curso_id = c.id
                  LEFT JOIN pagos p ON i.id = p.inscripcion_id
                  WHERE i.deleted_at IS NULL
                  ORDER BY i.fecha_inscripcion DESC";
                  
        $stmt = $db->prepare($query);
        $stmt->execute();
        $inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $inscripciones]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getInscripcionDetails($db, $id) {
    try {
        $query = "SELECT i.*, 
                         e.nombre as estudiante_nombre, e.apellido as estudiante_apellido, 
                         e.email as estudiante_email, e.telefono as estudiante_telefono,
                         s.nombre as salon_nombre, s.descripcion as salon_descripcion, 
                         s.precio as salon_precio, s.horario as salon_horario,
                         c.nombre as curso_nombre, c.descripcion as curso_descripcion,
                         p.estado as pago_estado, p.monto as pago_monto, p.fecha_pago
                  FROM inscripciones i
                  INNER JOIN estudiantes e ON i.estudiante_id = e.id
                  INNER JOIN salones s ON i.salon_id = s.id
                  INNER JOIN cursos c ON s.curso_id = c.id
                  LEFT JOIN pagos p ON i.id = p.inscripcion_id
                  WHERE i.id = ? AND i.deleted_at IS NULL";
                  
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        $inscripcion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($inscripcion) {
            echo json_encode(['success' => true, 'data' => $inscripcion]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Inscripción no encontrada']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}


// ... (existing code) ...
function updateInscripcion($db, $input) {
    try {
        $id = $_GET['id']; // Get the ID from the URL

        if (empty($id)) {
            throw new Exception("ID de inscripción no proporcionado para actualizar.");
        }

        // Sanitize and validate input data.  This is very important!
        $salon_id = $input['salon_id'] ?? null;
        $estudiante_id = $input['estudiante_id'] ?? null;
        $estado = $input['estado'] ?? null;
        $notas = $input['notas'] ?? null;

        // Build the SET part of the query dynamically
        $setClauses = [];
        $params = [];

        if ($salon_id !== null) {
            $setClauses[] = "salon_id = :salon_id";
            $params[':salon_id'] = $salon_id;
        }
        if ($estudiante_id !== null) {
            $setClauses[] = "estudiante_id = :estudiante_id";
            $params[':estudiante_id'] = $estudiante_id;
        }
        if ($estado !== null) {
            $setClauses[] = "estado = :estado";
            $params[':estado'] = $estado;
        }
        if ($notas !== null) {
            $setClauses[] = "notas = :notas";
            $params[':notas'] = $notas;
        }

        if (empty($setClauses)) {
            throw new Exception("No se proporcionaron datos para actualizar.");
        }

        $setQuery = implode(", ", $setClauses);

        // Prepare and execute the update query
        $sql = "UPDATE inscripciones SET $setQuery, updated_at = NOW() WHERE id = :id AND deleted_at IS NULL";
        $stmt = $db->prepare($sql);
        $params[':id'] = $id;  // Add the ID to the parameters
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Inscripción actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró la inscripción o no se realizaron cambios']);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}


function createInscripciones($db, $data) {
    try {
        $db->beginTransaction();
        
        $salon_id = $data['salon_id'];
        $estudiantes_ids = $data['estudiantes_ids'];
        $estado = $data['estado'] ?? 'Pendiente';
        $notas = $data['notas'] ?? '';
        $enviar_notificacion = $data['enviar_notificacion'] ?? false;
        
        $inscripciones_creadas = [];
        
        foreach ($estudiantes_ids as $estudiante_id) {
            // Verificar si ya existe inscripción
            $check_query = "SELECT id FROM inscripciones 
                           WHERE estudiante_id = ? AND salon_id = ? AND deleted_at IS NULL";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->execute([$estudiante_id, $salon_id]);
            
            if ($check_stmt->fetch()) {
                continue; // Saltar si ya existe
            }
            
            // Crear inscripción
            $insert_query = "INSERT INTO inscripciones (estudiante_id, salon_id, estado, notas, fecha_inscripcion, created_at) 
                            VALUES (?, ?, ?, ?, NOW(), NOW())";
            $insert_stmt = $db->prepare($insert_query);
            $insert_stmt->execute([$estudiante_id, $salon_id, $estado, $notas]);
            
            $inscripcion_id = $db->lastInsertId();
            $inscripciones_creadas[] = $inscripcion_id;
            
            // Si se requiere notificación
            if ($enviar_notificacion) {
                // Aquí puedes agregar la lógica para enviar emails
                enviarNotificacionInscripcion($db, $inscripcion_id);
            }
        }
        
        $db->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Inscripciones creadas exitosamente',
            'inscripciones_creadas' => count($inscripciones_creadas)
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function updateInscripcionStatus($db, $data) {
    try {
        $inscripcion_id = $data['inscripcion_id'];
        $nuevo_estado = $data['estado'];
        
        $query = "UPDATE inscripciones SET estado = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$nuevo_estado, $inscripcion_id]);
        
        echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function deleteInscripcion($db, $id) {
    try {
        // Soft delete
        $query = "UPDATE inscripciones SET deleted_at = NOW() WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => 'Inscripción eliminada correctamente']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function enviarNotificacionInscripcion($db, $inscripcion_id) {
    // Implementar lógica de envío de email
    // Por ahora solo log
    error_log("Notificación enviada para inscripción ID: " . $inscripcion_id);
}
?>
