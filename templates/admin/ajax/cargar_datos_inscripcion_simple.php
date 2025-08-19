<?php
// Versión simplificada para pruebas
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Datos de ejemplo para pruebas
    $response = [
        'estudiantes' => [
            [
                'id' => 1,
                'nombre' => 'María García Rodríguez',
                'email' => 'maria.garcia@email.com',
                'telefono' => '+57 300 123 4567'
            ],
            [
                'id' => 2,
                'nombre' => 'Carlos López Martínez',
                'email' => 'carlos.lopez@email.com',
                'telefono' => '+57 310 987 6543'
            ],
            [
                'id' => 3,
                'nombre' => 'Ana Sofía Herrera',
                'email' => 'ana.herrera@email.com',
                'telefono' => '+57 320 456 7890'
            ]
        ],
        'cursos' => [
            '1' => [
                'nombre' => 'Desarrollo Web Frontend',
                'salon' => 'Salón A',
                'precio' => 299,
                'duracion' => '8 semanas'
            ],
            '2' => [
                'nombre' => 'Python para Principiantes',
                'salon' => 'Salón B',
                'precio' => 199,
                'duracion' => '6 semanas'
            ],
            '3' => [
                'nombre' => 'Diseño UX/UI Avanzado',
                'salon' => 'Salón C',
                'precio' => 399,
                'duracion' => '12 semanas'
            ],
            '4' => [
                'nombre' => 'Marketing Digital',
                'salon' => 'Salón A',
                'precio' => 249,
                'duracion' => '10 semanas'
            ]
        ],
        'success' => true
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'estudiantes' => [],
        'cursos' => []
    ]);
}
?>
