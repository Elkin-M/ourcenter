<?php
// Script para probar el envío de correos
require_once __DIR__ . '/server/config.php';
require_once __DIR__ . '/helpers/security.php';

// Mostrar formulario simple si no se ha enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Prueba de Envío de Email</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
            .form-group { margin-bottom: 15px; }
            label { display: block; margin-bottom: 5px; font-weight: bold; }
            input[type="email"] { width: 100%; padding: 8px; box-sizing: border-box; }
            button { background-color: #0d6efd; color: white; border: none; padding: 10px 15px; cursor: pointer; }
            .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
            .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
            .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        </style>
    </head>
    <body>
        <h1>Prueba de Envío de Email</h1>
        <p>Este formulario te permitirá probar si tu configuración de email funciona correctamente.</p>
        
        <form method="post">
            <div class="form-group">
                <label for="email">Email de destino:</label>
                <input type="email" id="email" name="email" required placeholder="Ingresa tu email para recibir la prueba">
            </div>
            <button type="submit">Enviar email de prueba</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// Procesar el envío
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

if (!$email) {
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
            .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
            .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
            a { color: #0d6efd; text-decoration: none; }
        </style>
    </head>
    <body>
        <h1>Error</h1>
        <div class="alert alert-danger">
            Por favor, introduce un email válido.
        </div>
        <a href="' . $_SERVER['PHP_SELF'] . '">&laquo; Volver</a>
    </body>
    </html>';
    exit;
}

// Intentar enviar el correo
$resultado = sendActivationEmail(
    $email,
    'Usuario de Prueba',
    'token_de_prueba_' . time(),
    24
);

// Mostrar resultado
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado de la prueba</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        a { color: #0d6efd; text-decoration: none; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow: auto; }
    </style>
</head>
<body>
    <h1>Resultado de la prueba</h1>
    
    <?php if ($resultado): ?>
    <div class="alert alert-success">
        <h3>¡Email enviado correctamente!</h3>
        <p>Se ha enviado un correo de prueba a: <?php echo htmlspecialchars($email); ?></p>
        <p>Por favor, verifica tu bandeja de entrada (y la carpeta de spam si no lo encuentras).</p>
    </div>
    <?php else: ?>
    <div class="alert alert-danger">
        <h3>Error al enviar el email</h3>
        <p>No se pudo enviar el correo electrónico. Verifica la configuración de tu servidor SMTP.</p>
        <p>Revisa los logs de error de PHP para más información.</p>
    </div>
    <?php endif; ?>
    
    <h3>Datos de la configuración actual:</h3>
    <pre>
Host SMTP: <?php echo htmlspecialchars($config['mail']['host']); ?>
Puerto: <?php echo htmlspecialchars($config['mail']['port']); ?>
Cifrado: <?php echo htmlspecialchars($config['mail']['encryption']); ?>
Usuario: <?php echo htmlspecialchars($config['mail']['username']); ?>
Remitente: <?php echo htmlspecialchars($config['mail']['from_name'] . ' <' . $config['mail']['from_email'] . '>'); ?>
    </pre>
    
    <p><a href="<?php echo $_SERVER['PHP_SELF']; ?>">&laquo; Volver a la página de prueba</a></p>
</body>
</html>