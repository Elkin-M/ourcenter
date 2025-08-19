<?php
// Configuración de headers para AJAX
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir archivos de configuración
require_once '../../../config/db.php';
require_once '../../../config/init.php';

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Verificar si el usuario está autenticado como administrador
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        throw new Exception('Acceso no autorizado');
    }

    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Obtener datos del POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Datos inválidos');
    }

    // Validar datos requeridos
    $cursoId = $input['curso_id'] ?? null;
    $estudiantesIds = $input['estudiantes_ids'] ?? [];
    $estado = $input['estado'] ?? 'Pendiente';
    $metodoInscripcion = $input['metodo_inscripcion'] ?? 'Online';
    $enviarNotificacion = $input['enviar_notificacion'] ?? false;
    $notas = $input['notas'] ?? '';

    if (!$cursoId || empty($estudiantesIds)) {
        throw new Exception('Datos requeridos faltantes');
    }

    // Verificar que el curso existe y está activo
    $stmt = $pdo->prepare("SELECT id, nombre, precio FROM cursos WHERE id = ? AND estado = 'activo'");
    $stmt->execute([$cursoId]);
    $curso = $stmt->fetch();

    if (!$curso) {
        throw new Exception('Curso no encontrado o inactivo');
    }

    // Verificar que los estudiantes existen
    $placeholders = str_repeat('?,', count($estudiantesIds) - 1) . '?';
    $stmt = $pdo->prepare("SELECT id, nombre, apellido, email FROM usuarios WHERE id IN ($placeholders) AND rol = 'estudiante'");
    $stmt->execute($estudiantesIds);
    $estudiantes = $stmt->fetchAll();

    if (count($estudiantes) !== count($estudiantesIds)) {
        throw new Exception('Algunos estudiantes no fueron encontrados');
    }

    // Iniciar transacción
    $pdo->beginTransaction();

    $inscripcionesCreadas = [];
    $fechaActual = date('Y-m-d H:i:s');

    foreach ($estudiantes as $estudiante) {
        // Verificar si ya existe una inscripción para este estudiante en este curso
        $stmt = $pdo->prepare("SELECT id FROM inscripciones WHERE estudiante_id = ? AND curso_id = ? AND estado != 'Cancelada'");
        $stmt->execute([$estudiante['id'], $cursoId]);
        
        if ($stmt->fetch()) {
            throw new Exception("El estudiante {$estudiante['nombre']} {$estudiante['apellido']} ya está inscrito en este curso");
        }

        // Crear inscripción
        $stmt = $pdo->prepare("
            INSERT INTO inscripciones (
                estudiante_id, curso_id, fecha_inscripcion, estado, 
                metodo_inscripcion, notas, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $estudiante['id'],
            $cursoId,
            $fechaActual,
            $estado,
            $metodoInscripcion,
            $notas,
            $fechaActual,
            $fechaActual
        ]);

        $inscripcionId = $pdo->lastInsertId();

        // Crear registro de pago pendiente
        $stmt = $pdo->prepare("
            INSERT INTO pagos (
                inscripcion_id, monto, estado, fecha_vencimiento, 
                metodo_pago, created_at, updated_at
            ) VALUES (?, ?, 'Pendiente', DATE_ADD(NOW(), INTERVAL 7 DAY), 'Por definir', ?, ?)
        ");
        
        $stmt->execute([
            $inscripcionId,
            $curso['precio'],
            $fechaActual,
            $fechaActual
        ]);

        $inscripcionesCreadas[] = [
            'id' => $inscripcionId,
            'estudiante' => $estudiante['nombre'] . ' ' . $estudiante['apellido'],
            'email' => $estudiante['email']
        ];

        // Enviar notificación por email si está habilitado
        if ($enviarNotificacion) {
            enviarNotificacionInscripcion($estudiante, $curso, $estado);
        }
    }

    // Confirmar transacción
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Inscripciones creadas exitosamente',
        'inscripciones_creadas' => $inscripcionesCreadas,
        'total_creadas' => count($inscripcionesCreadas)
    ]);

} catch (Exception $e) {
    // Revertir transacción si hay error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function enviarNotificacionInscripcion($estudiante, $curso, $estado) {
    // Aquí implementarías el envío de email
    // Por ahora solo registramos en el log
    error_log("Notificación enviada a {$estudiante['email']} para inscripción en {$curso['nombre']}");
}
?>
