<?php
// Añadir estas líneas al archivo config.php para configurar el email
return [
    'base_url' => 'http://localhost/ourcenter/',
    'app_name' => 'OurCenter',
    'db' => [
        'host' => 'localhost',
        'dbname' => 'ourcenter',
        'user' => 'root',
        'password' => '',
    ],
    'mail' => [
        'host' => 'smtp.gmail.com',  // Cambia esto según tu proveedor de correo
        'username' => 'elkinmb3@gmail.com',  // Tu correo electrónico
        'password' => 'kvfu plfo zprd rqfm',  // Tu contraseña o contraseña de aplicación
        'port' => 587,  // Puerto SMTP (587 para TLS, 465 para SSL)
        'encryption' => 'tls',  // 'tls' o 'ssl'
        'from_email' => 'noreply@tudominio.com',  // Correo remitente
        'from_name' => 'OurCenter',  // Nombre remitente
    ],
];
?>