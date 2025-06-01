
<?php
/**
 * validator.php - Funciones de validación para formularios
 */

/**
 * Valida un formato de fecha
 * 
 * @param string $date Fecha a validar
 * @param string $format Formato esperado (por defecto Y-m-d)
 * @return bool True si la fecha es válida, false en caso contrario
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Valida si un valor está en un array de opciones válidas
 * 
 * @param mixed $value Valor a validar
 * @param array $options Array de opciones válidas
 * @return bool True si el valor está en las opciones, false en caso contrario
 */
function validateInOptions($value, $options) {
    return in_array($value, $options, true);
}

/**
 * Valida un número de teléfono
 * 
 * @param string $phone Número a validar
 * @return bool True si el formato es válido, false en caso contrario
 */
function validatePhone($phone) {
    return preg_match('/^[0-9+\-\s]{7,15}$/', $phone);
}

/**
 * Valida un documento de identidad (básica)
 * 
 * @param string $document Documento a validar
 * @return bool True si el formato es válido, false en caso contrario
 */
function validateDocument($document) {
    // Eliminar espacios y guiones
    $document = str_replace([' ', '-'], '', $document);
    // Verificar que solo tenga números y letras
    return preg_match('/^[0-9A-Za-z]{5,20}$/', $document);
}

/**
 * Valida una contraseña según los criterios de seguridad
 * 
 * @param string $password Contraseña a validar
 * @return bool True si cumple con los criterios, false en caso contrario
 */
function validatePassword($password) {
    // Mínimo 8 caracteres, al menos un número y una letra
    return strlen($password) >= 8 && 
           preg_match('/[0-9]/', $password) && 
           preg_match('/[a-zA-Z]/', $password);
}

?>