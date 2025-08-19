<?php
// obtener_estudiante.php

header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/db.php';

// Verifica si se envió el parámetro "id_estudiante"
if (!isset($_POST['id_estudiante']) || empty($_POST['id_estudiante'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de estudiante requerido'
    ]);
    exit;
}

$id = intval($_POST['id_estudiante']);

// Log para depuración en el servidor
error_log("📥 ID de estudiante recibido: " . $id);

try {
    // Consulta del estudiante
    $sql = "SELECT * FROM usuarios WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($estudiante) {
        // Consulta de inscripciones
        $sqlInscripciones = "SELECT i.*, c.nombre AS curso_nombre, c.descripcion AS curso_descripcion 
                             FROM inscripciones i
                             INNER JOIN cursos c ON i.curso_id = c.id
                             WHERE i.id = :id";
        $stmtInscripciones = $pdo->prepare($sqlInscripciones);
        $stmtInscripciones->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtInscripciones->execute();
        $inscripciones = $stmtInscripciones->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Estudiante encontrado',
            'id_recibido' => $id,
            'estudiante' => $estudiante,
            'inscripciones' => $inscripciones
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Estudiante no encontrado',
            'id_recibido' => $id
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la consulta: ' . $e->getMessage(),
        'id_recibido' => $id
    ]);
}
