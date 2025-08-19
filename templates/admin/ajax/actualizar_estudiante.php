<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');
require_once '../../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // 🔍 Si solo se manda el ID, es para obtener datos del estudiante
    if (
        !isset($_POST['nombre']) &&
        !isset($_POST['apellido']) &&
        !isset($_POST['email']) &&
        !isset($_POST['telefono'])
    ) {
        try {
            $stmt = $pdo->prepare("SELECT id, nombre, apellido, email, telefono FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($estudiante) {
                echo json_encode(['success' => true, 'estudiante' => $estudiante]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Estudiante no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al consultar: ' . $e->getMessage()]);
        }
        exit;
    }

    // ✏️ Si llegan más datos, es para actualizar
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Validaciones
    if (empty($nombre) || empty($apellido) || empty($email) || empty($telefono)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido']);
        exit;
    }

    if (!preg_match('/^[0-9]{7,15}$/', $telefono)) {
        echo json_encode(['success' => false, 'message' => 'Número de teléfono inválido']);
        exit;
    }

    if (strlen($nombre) > 100 || strlen($apellido) > 100 || strlen($email) > 150 || strlen($telefono) > 15) {
        echo json_encode(['success' => false, 'message' => 'Uno o más campos exceden la longitud permitida']);
        exit;
    }

    try {
        // Validar que no haya otro usuario con el mismo email
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Este email ya está registrado por otro usuario']);
            exit;
        }

        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                UPDATE usuarios 
                SET nombre = ?, apellido = ?, email = ?, telefono = ?, password = ?, fecha_actualizacion = NOW()
                WHERE id = ? AND rol_id = 2
            ");
            $result = $stmt->execute([$nombre, $apellido, $email, $telefono, $password_hash, $id]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE usuarios 
                SET nombre = ?, apellido = ?, email = ?, telefono = ?, fecha_actualizacion = NOW()
                WHERE id = ? AND rol_id = 2
            ");
            $result = $stmt->execute([$nombre, $apellido, $email, $telefono, $id]);
        }

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Estudiante actualizado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el estudiante']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida']);
}
