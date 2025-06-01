<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');
require 'db.php'; // Aquí se establece la conexión PDO con la base de datos

// Verificar si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['pass'] ?? ''); // Fixed the field name to match your form

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Todos los campos son obligatorios.";
        header("Location: ../sesion.php");
        exit();
    }

    try {
        $sql = "SELECT * FROM usuarios WHERE email = :email AND estado = 'activo' LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['password'])) {
            // Inicio de sesión exitoso
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['rol_id'];
            $_SESSION['rol_id'] = $usuario['rol_id'];

            // Actualizar última sesión
            $sqlUpdate = "UPDATE usuarios SET ultima_sesion = NOW() WHERE id = :id";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute(['id' => $usuario['id']]);

            if ($usuario['rol_id'] == 1 ) {
                $_SESSION['admin'] = true; // Asignar variable de sesión para admin
                header("Location: ../templates/dashboard.php");
            exit();
            } elseif ($usuario['rol_id'] == 2) {
                $_SESSION['admin'] = false; // Asignar variable de sesión para usuario normal
                header("Location: ../templates/estudiante/dashboard.php");
                exit();
            }elseif ($usuario['rol_id'] == 3) {
                $_SESSION['admin'] = false; // Asignar variable de sesión para usuario normal
                header("Location: ../templates/profesor/dashboard.php");
                exit();
            }
            exit();
        } else {
            $_SESSION['error'] = "Credenciales inválidas o usuario inactivo.";
            header("Location: ../sesion.php");  
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error en el login: " . $e->getMessage());
        $_SESSION['error'] = "Ocurrió un error al procesar el login.";
        header("Location: ../sesion.php");
        exit();
    }
} 

// No output here - any output should be handled via redirects
?>