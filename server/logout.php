<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');
 // Inicia la sesión si no está iniciada



// Destruir todas las variables de sesión
$_SESSION = array();

// Si se desea destruir completamente la sesión, también se deben eliminar las cookies de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Finalmente, destruir la sesión
session_destroy();

// Redirigir al usuario a la página de login
header("Location: /ourcenter/index.php");
exit;
?>
