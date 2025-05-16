<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');

require_once '../../config/db.php';

$sql = "SELECT sc.nombre, sc.email, sc.telefono, c.nombre AS curso, sc.mensaje, sc.estado, sc.fecha_creacion 
        FROM solicitudes_contacto sc 
        LEFT JOIN cursos c ON sc.curso_interes_id = c.id 
        ORDER BY sc.fecha_creacion DESC";
$stmt = $pdo->query($sql);
$solicitudes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitudes - Our Center</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" href="../../images/favicon/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" sizes="180x180" href="../../images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../../images/favicon/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="../../images/favicon/android-chrome-512x512.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../images/favicon/favicon-16x16.png">
    <link rel="manifest" href="../../images/favicon/site.webmanifest">
    <meta name="theme-color" content="#0a1b5c">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/mstile-144x144.png">
    <link rel="stylesheet" href="../../css/dashboard.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    
</head>
<body>
<!-- Preloader (mantenido del original) -->
    <?php include '../preloader.php';?>
    
    <!-- Sidebar Toggle Button -->
    <div class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Sidebar Navigation -->
    <div class="sidebar" id="sidebar">
        <div class="logo-container">
            <div class="logo">
                <img src="../../images/logo.webp" alt="Logo de Our Center">
                <div class="logo-text" style="margin-left: 10px;">OUR CENTER</div>
            </div>
        </div>
        
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link" href="/ourcenter/templates/dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/ourcenter/templates/admin/Usuarios.php">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Usuarios</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/ourcenter/templates/admin/cursos.php">
                    <i class="fas fa-book"></i>
                    <span class="nav-text">Cursos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/ourcenter/templates/admin/inscripciones.php">
                    <i class="fas fa-user-graduate"></i>
                    <span class="nav-text">Inscripciones</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/ourcenter/templates/admin/pagos.php">
                    <i class="fas fa-credit-card"></i>
                    <span class="nav-text">Pagos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="/ourcenter/templates/admin/solicitudes.php">
                    <i class="fas fa-envelope"></i>
                    <span class="nav-text">Solicitudes</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/ourcenter/templates/admin/reportes.php">
                    <i class="fas fa-chart-bar"></i>
                    <span class="nav-text">Reportes</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/ourcenter/templates/admin/configuracion.php">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">Configuración</span>
                </a>
            </li>
        </ul>
    </div>
        
    <!-- Notificaciones Alert Box -->
    <div class="alert-box" id="alertBox">
        <div class="alert-header">
            <span>Notificaciones</span>
            <i class="fas fa-times" id="closeAlertBox" style="cursor: pointer;"></i>
        </div>
        <div class="alert-body">
            <div class="alert-message">
                <div class="d-flex justify-content-between mb-1">
                    <strong><span class="status-indicator status-new"></span> Nueva solicitud</strong>
                    <small class="text-muted">Hace 10 min</small>
                </div>
                <p class="mb-0">María González está interesada en Teen English</p>
            </div>
            <div class="alert-message">
                <div class="d-flex justify-content-between mb-1">
                    <strong><span class="status-indicator status-process"></span> Pago pendiente</strong>
                    <small class="text-muted">Hace 2 horas</small>
                </div>
                <p class="mb-0">Carlos Pérez tiene un pago pendiente de vencimiento</p>
            </div>
        </div>
    </div>
    
    <?php include '../header.php'; ?>



<div class="container py-4">
    <h2 class="mb-4">Solicitudes de Contacto</h2>

    <div class="table-responsive">
        <table id="tablaSolicitudes" class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Curso de Interés</th>
                    <th>Mensaje</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($solicitudes as $solicitud): ?>
                    <tr>
                        <td><?= htmlspecialchars($solicitud['nombre']) ?></td>
                        <td><?= htmlspecialchars($solicitud['email']) ?></td>
                        <td><?= htmlspecialchars($solicitud['telefono']) ?></td>
                        <td><?= htmlspecialchars($solicitud['curso'] ?? 'No especificado') ?></td>
                        <td><?= nl2br(htmlspecialchars($solicitud['mensaje'])) ?></td>
                        <td>
                            <?php
                                $estado = $solicitud['estado'];
                                $badge = match ($estado) {
                                    'nuevo' => 'primary',
                                    'en_proceso' => 'warning text-dark',
                                    'completado' => 'success',
                                    'cancelado' => 'danger',
                                    default => 'secondary'
                                };
                            ?>
                            <span class="badge bg-<?= $badge ?>"><?= ucfirst($estado) ?></span>
                        </td>
                        <td><?= date("d/m/Y H:i", strtotime($solicitud['fecha_creacion'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
            <?php include '../footer.php'; ?>

</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('#tablaSolicitudes').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            }
        });
    });
</script>

</body>
</html>
