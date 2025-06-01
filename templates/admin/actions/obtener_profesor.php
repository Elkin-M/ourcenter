<?php
// ajax/obtener_profesor.php
require_once '../../../config/db.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("
    SELECT u.*, 
           COUNT(DISTINCT s.id) as salones_asignados,
           COUNT(DISTINCT cs.curso_id) as cursos_asignados,
           GROUP_CONCAT(DISTINCT s.nombre SEPARATOR ', ') as nombres_salones
    FROM usuarios u
    LEFT JOIN salones s ON u.id = s.teacher_id
    LEFT JOIN curso_salones cs ON s.id = cs.salon_id
    WHERE u.id = ? AND u.rol_id = 3
    GROUP BY u.id
");
$stmt->execute([$id]);
$profesor = $stmt->fetch();

header('Content-Type: application/json');
echo json_encode($profesor);
?>