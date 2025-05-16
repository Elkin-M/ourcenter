<?php
// Detectar página actual si no se define
if (!isset($current_page)) {
    $current_page = basename($_SERVER['PHP_SELF'], ".php");
}

// Conexión a la base de datos (ruta corregida)
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../config/conexion-courses.php';

// Obtener el número de nuevas solicitudes
$nuevas_solicitudes = $pdo->query("SELECT COUNT(*) FROM solicitudes_contacto WHERE estado = 'nuevo'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $current_page ?> - Our Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/ourcenter/css/dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

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

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Preloader -->

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <div class="dashboard-header">
            <div>
                <h1 class="page-title" style="color:white;"><?= $current_page ?></h1>
            </div>
            <div class="d-flex align-items-center">
                <!-- Notificaciones -->
                <div class="notifications me-4">
                    <i class="fas fa-bell notifications-icon" style="color: white;" id="notificationsBtn"></i>
                    <span class="notifications-badge"><?= $nuevas_solicitudes ?></span>
                </div>

                <!-- User Dropdown -->
                <div class="user-dropdown dropdown">
                    <button class="dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="../../images/2.jpg" alt="Usuario">
                        <span><?php echo $_SESSION['usuario_nombre']; ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li class="user-header">
                            <div class="user-name"><?php echo $_SESSION['usuario_nombre']; ?></div>
                            <div class="user-role">Estudiante</div>
                        </li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user"></i> Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog"></i> Configuración</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../../server/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
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

        <!-- Bootstrap Bundle -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Scripts -->
        <script>
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const body = document.body;

            const sidebarOverlay = document.createElement('div');
            sidebarOverlay.className = 'sidebar-overlay';
            document.body.appendChild(sidebarOverlay);

            function toggleSidebar() {
                sidebar.classList.toggle('show');
                body.classList.toggle('sidebar-hidden');
                if (window.innerWidth < 768) {
                    sidebarOverlay.classList.toggle('active');
                }
            }

            sidebarToggle.addEventListener('click', toggleSidebar);
            sidebarOverlay.addEventListener('click', () => {
                if (sidebar.classList.contains('show') && window.innerWidth < 768) {
                    toggleSidebar();
                }
            });

            function adjustForScreenSize() {
                if (window.innerWidth < 768) {
                    sidebar.classList.remove('show');
                    body.classList.add('sidebar-hidden');
                    mainContent.style.marginLeft = '0';
                } else {
                    if (!body.classList.contains('sidebar-hidden-by-user')) {
                        sidebar.classList.add('show');
                        body.classList.remove('sidebar-hidden');
                    }
                }
            }

            sidebarToggle.addEventListener('click', () => {
                if (window.innerWidth >= 768) {
                    if (sidebar.classList.contains('show')) {
                        body.classList.remove('sidebar-hidden-by-user');
                    } else {
                        body.classList.add('sidebar-hidden-by-user');
                    }
                }
            });

            window.addEventListener('load', adjustForScreenSize);
            window.addEventListener('resize', adjustForScreenSize);

            const notificationsBtn = document.getElementById('notificationsBtn');
            const alertBox = document.getElementById('alertBox');
            const closeAlertBox = document.getElementById('closeAlertBox');

            notificationsBtn.addEventListener('click', () => {
                alertBox.classList.toggle('show');
            });

            closeAlertBox.addEventListener('click', () => {
                alertBox.classList.remove('show');
            });

            document.addEventListener('click', (event) => {
                if (!alertBox.contains(event.target) && event.target !== notificationsBtn) {
                    alertBox.classList.remove('show');
                }
            });
        </script>
