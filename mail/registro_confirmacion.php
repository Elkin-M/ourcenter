<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');

/**
 * registro_confirmacion.php - Página de confirmación tras el registro exitoso
 * 
 * Esta página muestra un mensaje de confirmación al usuario después de completar
 * el registro y le proporciona instrucciones sobre los siguientes pasos.
 */

// Iniciar sesión para acceder a los mensajes flash

// Si no hay mensaje de éxito en la sesión, redirigir al formulario de registro
if (!isset($_SESSION['success_message'])) {
    header('Location: ../templates/registro.php');
    exit;
}

// Capturar el mensaje de éxito y eliminarlo de la sesión
$successMessage = $_SESSION['success_message'];
unset($_SESSION['success_message']);

?>
<?php 
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');
 // Iniciar sesión para acceder a $_SESSION
// Detectar página actual si no se define
if (!isset($current_page)) {
    $current_page = basename($_SERVER['PHP_SELF'], ".php");
}

// Conexión a la base de datos (ruta corregida)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/conexion-courses.php';

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
    <link rel="stylesheet" href="../css/dashboard.css">
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
<span><?= isset($_SESSION['usuario_nombre']) ? htmlspecialchars($_SESSION['usuario_nombre']) : 'Administrador'; ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li class="user-header">
                            <div class="user-name"><?= isset($_SESSION['usuario_nombre']) ? htmlspecialchars($_SESSION['usuario_nombre']) : 'Usuario'; ?></div>
                            <div class="user-role">Administrador</div>
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

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">¡Registro Exitoso!</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    
                    <div class="alert alert-success" role="alert">
                        <?php echo $successMessage; ?>
                    </div>
                    
                    <div class="mb-4">
                        <h5>Próximos pasos:</h5>
                        <ol class="list-group list-group-numbered mt-3">
                            <li class="list-group-item">Revisa tu correo electrónico (incluyendo la carpeta de spam) para encontrar el enlace de activación.</li>
                            <li class="list-group-item">Haz clic en el enlace para activar tu cuenta.</li>
                            <li class="list-group-item">Una vez activada, podrás iniciar sesión y explorar los cursos disponibles.</li>
                        </ol>
                    </div>
                    
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Si no recibes el correo de activación en los próximos 15 minutos, 
                        <a href="reenviar_activacion.php" class="alert-link">haz clic aquí para solicitar un nuevo correo</a>.
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="../index.php" class="btn btn-primary">Volver a la página principal</a>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <p class="mb-0">¿Necesitas ayuda? <a href="contacto.php">Contáctanos</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir la plantilla de pie de página
include __DIR__ . '/../templates/footer.php';
?>