<?php
// Archivo autoload.php simple para cargar las clases de PHPMailer sin Composer
spl_autoload_register(function ($className) {
    // Convertir namespace a ruta de archivo
    $prefix = 'PHPMailer\\PHPMailer\\';
    $len = strlen($prefix);
    
    if (strncmp($prefix, $className, $len) !== 0) {
        // La clase no comienza con el prefijo del namespace de PHPMailer
        return;
    }
    
    // Obtener la ruta relativa de la clase
    $relativeClass = substr($className, $len);
    
    // Reemplazar los separadores de namespace por separadores de directorio
    // y añadir .php
    $file = __DIR__ . '/phpmailer/phpmailer/src/' . str_replace('\\', '/', $relativeClass) . '.php';
    
    // Si el archivo existe, cargarlo
    if (file_exists($file)) {
        require $file;
    }
});
?>