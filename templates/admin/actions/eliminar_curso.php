<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');

// eliminar_curso.php
require_once '../../config/db.php';

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// Verificar que se proporcione el ID del curso
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de curso no válido.']);
    exit;
}

$curso_id = (int)$_POST['id'];

try {
    // Comenzar transacción
    $pdo->beginTransaction();
    
    // Verificar si el curso existe y obtener información
    $stmt = $pdo->prepare("SELECT id, nombre, imagen_url FROM cursos WHERE id = ?");
    $stmt->execute([$curso_id]);
    $curso = $stmt->fetch();
    
    if (!$curso) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Curso no encontrado.']);
        exit;
    }
    
    // Verificar si hay inscripciones activas
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM inscripciones WHERE curso_id = ?");
    $stmt->execute([$curso_id]);
    $inscripciones = $stmt->fetch();
    
    if ($inscripciones['total'] > 0) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false, 
            'message' => 'No se puede eliminar el curso porque tiene estudiantes inscritos. Primero debe cancelar las inscripciones.'
        ]);
        exit;
    }
    
    // Eliminar registros relacionados (si los hay)
    // Por ejemplo, calificaciones, asistencias, etc.
    $stmt = $pdo->prepare("DELETE FROM calificaciones WHERE curso_id = ?");
    $stmt->execute([$curso_id]);
    
    $stmt = $pdo->prepare("DELETE FROM asistencias WHERE curso_id = ?");
    $stmt->execute([$curso_id]);
    
    // Eliminar el curso
    $stmt = $pdo->prepare("DELETE FROM cursos WHERE id = ?");
    $stmt->execute([$curso_id]);
    
    // Eliminar imagen si existe y no es la por defecto
    if (!empty($curso['imagen_url']) && $curso['imagen_url'] !== '/images/cursos/default-course.jpg') {
        $image_path = '../../' . ltrim($curso['imagen_url'], '/');
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // Confirmar transacción
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'El curso "' . htmlspecialchars($curso['nombre']) . '" ha sido eliminado exitosamente.'
    ]);
    
} catch (PDOException $e) {
    // Revertir transacción en caso de error
    $pdo->rollBack();
    
    error_log("Error al eliminar curso: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error interno del servidor. No se pudo eliminar el curso.'
    ]);
}
?>