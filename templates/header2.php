<?php 
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');

// Detectar página actual si no se define
if (!isset($current_page)) {
    $current_page = basename($_SERVER['PHP_SELF'], ".php");
}

// Conexión a la base de datos (ruta corregida)
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/conexion-courses.php';
date_default_timezone_set('America/Bogota');

// // Verificar que el usuario está logueado y es administrador
// if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] !== 1) {
//     header('Location: ../../login.php?redirect=admin');
//     exit();
// }

// Obtener información del administrador
$admin_id = $_SESSION['usuario_id'];

$query = "SELECT u.*, r.nombre as rol_nombre 
          FROM usuarios u 
          JOIN roles r ON u.rol_id = r.id
          WHERE u.id = ? AND u.rol_id = 1 AND u.estado = 'activo'";
$stmt = $pdo->prepare($query);
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    session_destroy();
    header('Location: /ourcenter/sesion.php');
    exit();
}
// Validar que todos los datos necesarios estén disponibles
if (!$admin['nombre'] || !$admin['email']) {
    error_log("Datos de administrador incompletos para ID: " . $admin_id);
    session_destroy();
    header('Location: /ourcenter/sesion.php?error=datos_incompletos');
    exit();
}

// Obtener el número de nuevas solicitudes
$nuevas_solicitudes = $pdo->query("SELECT COUNT(*) FROM solicitudes_contacto WHERE estado = 'nuevo'")->fetchColumn();

// Obtener estadísticas generales con manejo de errores
try {
    $stats_query = "SELECT 
        (SELECT COUNT(*) FROM usuarios WHERE rol_id = 3 AND estado = 'activo') as total_estudiantes,
        (SELECT COUNT(*) FROM usuarios WHERE rol_id = 2 AND estado = 'activo') as total_profesores,
        (SELECT COUNT(*) FROM cursos WHERE estado = 'activo') as total_cursos,
        (SELECT COUNT(*) FROM salones WHERE estado = 'activo') as total_salones,
        (SELECT COUNT(*) FROM inscripciones WHERE estado = 'activo') as total_inscripciones";

    $stats_stmt = $pdo->prepare($stats_query);
    $stats_stmt->execute();
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Valores por defecto en caso de error
    if (!$stats) {
        $stats = [
            'total_estudiantes' => 0,
            'total_profesores' => 0,
            'total_cursos' => 0,
            'total_salones' => 0,
            'total_inscripciones' => 0
        ];
    }
} catch (PDOException $e) {
    error_log("Error al obtener estadísticas: " . $e->getMessage());
    $stats = [
        'total_estudiantes' => 0,
        'total_profesores' => 0,
        'total_cursos' => 0,
        'total_salones' => 0,
        'total_inscripciones' => 0
    ];
}

// Obtener notificaciones y solicitudes pendientes
try {
    // Contar solicitudes nuevas
    $nuevas_solicitudes_stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_contacto WHERE estado = 'nuevo'");
    $nuevas_solicitudes_stmt->execute();
    $nuevas_solicitudes = $nuevas_solicitudes_stmt->fetchColumn() ?: 0;

    // Contar notificaciones no leídas
    $notificaciones_query = "SELECT COUNT(*) as total FROM notificaciones 
                             WHERE usuario_destino_id = ? AND leida = 0";
    $notif_stmt = $pdo->prepare($notificaciones_query);
    $notif_stmt->execute([$admin_id]);
    $notificaciones = $notif_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$notificaciones) {
        $notificaciones = ['total' => 0];
    }
} catch (PDOException $e) {
    error_log("Error al obtener notificaciones: " . $e->getMessage());
    $nuevas_solicitudes = 0;
    $notificaciones = ['total' => 0];
}

// Funciones de utilidad para el template

function formatNumber($number) {
    return number_format($number, 0, ',', '.');
}

function getTimeAgo($datetime) {
    $time = time() - strtotime($datetime);

    if ($time < 60) return 'Hace ' . $time . ' segundos';
    if ($time < 3600) return 'Hace ' . floor($time / 60) . ' minutos';
    if ($time < 86400) return 'Hace ' . floor($time / 3600) . ' horas';
    if ($time < 2592000) return 'Hace ' . floor($time / 86400) . ' días';

    return date('d/m/Y', strtotime($datetime)); // Retorna la fecha si es más de 30 días
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>OurCenter | Panel de Administrador</title>
    
    <!-- Favicon -->
    <link rel="icon" href="/ourcenter/images/favicon/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" sizes="180x180" href="/ourcenter/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/ourcenter/images/favicon/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/ourcenter/images/favicon/android-chrome-512x512.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/ourcenter/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/ourcenter/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="/ourcenter/images/favicon/site.webmanifest">
    <meta name="theme-color" content="#0a1b5c">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/mstile-144x144.png">

    <!-- CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ourcenter/css/dashboard.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Toastr para notificaciones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- SweetAlert2 para confirmaciones -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>   
    <!-- JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <?php if (isset($page_css)): ?>
        <?php foreach ($page_css as $css): ?>
            <link href="<?php echo $css; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($page_head_scripts)): ?>
        <?php foreach ($page_head_scripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="bg-light">

<!-- Sidebar -->
<div class="sidebar bg-white shadow-sm d-flex flex-column" id="sidebar">
    <?php
    $corporation_logo_url = '/ourcenter/images/logo.webp';
    $corporation_name = 'OURCENTER';
    ?>

    <div class="p-3 border-bottom" style="padding: 28px !important;">
        <div class="d-flex align-items-center">
            <div class="corporation-logo me-2">
                <?php if (!empty($corporation_logo_url)): ?>
                    <img src="<?php echo $corporation_logo_url; ?>" alt="Logo Corporación" class="rounded" style="height:40px;">
                <?php else: ?>
                    <div class="logo-placeholder rounded bg-primary text-white d-flex justify-content-center align-items-center" style="width:40px; height:40px;">
                        <?php echo strtoupper(substr($corporation_name, 0, 1)); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="corporation-info" style="color:black;">
            <h6 class="mb-0 text-truncate"><?php echo htmlspecialchars($corporation_name); ?></h6>
            </div>
        </div>
    </div>
    
    <div class="sidebar-menu flex-grow-1 overflow-auto">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>" href="/ourcenter/templates/dashboard.php">
                    <i class="fas fa-home me-2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="nav-header">GESTIÓN DE USUARIOS</li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'estudiantes' ? 'active' : ''; ?>" href="/ourcenter/templates/admin/estudiantes.php">
                    <i class="fas fa-users me-2"></i>
                    <span>Estudiantes</span>
                    <span class="badge rounded-pill bg-info ms-auto"><?php echo $stats['total_estudiantes']; ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'profesores' ? 'active' : ''; ?>" href="/ourcenter/templates/admin/profesores.php">
                    <i class="fas fa-chalkboard-teacher me-2"></i>
                    <span>Profesores</span>
                    <span class="badge rounded-pill bg-success ms-auto"><?php echo $stats['total_profesores']; ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'administradores' ? 'active' : ''; ?>" href="/ourcenter/templates/admin/administradores.php">
                    <i class="fas fa-user-shield me-2"></i>
                    <span>Administradores</span>
                </a>
            </li>
            
            <li class="nav-header">GESTIÓN ACADÉMICA</li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'cursos' ? 'active' : ''; ?>" href="/ourcenter/templates/admin/cursos.php">
                    <i class="fas fa-book me-2"></i>
                    <span>Cursos</span>
                    <span class="badge rounded-pill bg-primary ms-auto"><?php echo $stats['total_cursos']; ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'salones' ? 'active' : ''; ?>" href="/ourcenter/templates/admin/salones.php">
                    <i class="fas fa-chalkboard me-2"></i>
                    <span>Salones</span>
                    <span class="badge rounded-pill bg-warning ms-auto"><?php echo $stats['total_salones']; ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'horarios' ? 'active' : ''; ?>" href="/ourcenter/templates/admin/horarios.php">
                    <i class="fas fa-calendar-alt me-2"></i>
                    <span>Horarios</span>
                </a>
            </li>
            
            <li class="nav-header">COMUNICACIÓN</li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'solicitudes' ? 'active' : ''; ?>" href="/ourcenter/templates/admin/solicitudes.php">
                    <i class="fas fa-envelope me-2"></i>
                    <span>Solicitudes</span>
                    <?php if ($nuevas_solicitudes > 0): ?>
                        <span class="badge rounded-pill bg-danger ms-auto"><?php echo $nuevas_solicitudes; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'mensajes' ? 'active' : ''; ?>" href="/ourcenter/templates/admin/mensajes.php">
                    <i class="fas fa-comments me-2"></i>
                    <span>Mensajes</span>
                </a>
            </li>
            
            <li class="nav-header">REPORTES Y ANÁLISIS</li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'reportes' ? 'active' : ''; ?>" href="/ourcenter/templates/admin/reportes.php">
                    <i class="fas fa-chart-bar me-2"></i>
                    <span>Reportes</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'pagos' ? 'active' : ''; ?>" href="/ourcenter/templates/admin/pagos.php">
                    <i class="fas fa-credit-card me-2"></i>
                    <span>Pagos</span>
                </a>
            </li>
            
            <li class="nav-header">CONFIGURACIÓN</li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'configuracion' ? 'active' : ''; ?>" href="/ourcenter/templates/admin/configuracion.php">
                    <i class="fas fa-cog me-2"></i>
                    <span>Configuración</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'respaldos' ? 'active' : ''; ?>" href="/ourcenter/templates/admin/respaldos.php">
                    <i class="fas fa-database me-2"></i>
                    <span>Respaldos</span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="sidebar-footer p-3 border-top">
        <a href="/ourcenter/server/logout.php" class="btn btn-outline-secondary btn-sm d-block">
            <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
        </a>
    </div>
</div>

<!-- Contenido principal -->
<div class="main-content" id="mainContent">
    <!-- Navbar superior -->
    <nav class="navbar navbar-expand-lg navbar-dark py-2 px-3 shadow-sm" style="padding: 24px !important; background-color: #0a1b5c !important;">
        <div class="container-fluid px-0">
            
            <!-- Título de la página -->
            <div class="d-flex align-items-center">
                <h1 class="page-title mb-0 text-white"><?php echo ucfirst(str_replace('_', ' ', $current_page)); ?></h1>
            </div>
            
            <!-- Componentes del navbar derecho -->
            <ul class="navbar-nav ms-auto d-flex flex-row align-items-center">
                <!-- Accesos rápidos -->
                <li class="nav-item dropdown me-3">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-rocket me-1"></i>
                        <span class="d-none d-md-inline">Accesos Rápidos</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Acciones Rápidas</h6></li>
                        <li><a class="dropdown-item" href="estudiantes.php?action=new"><i class="fas fa-user-plus me-2"></i>Nuevo Estudiante</a></li>
                        <li><a class="dropdown-item" href="profesores.php?action=new"><i class="fas fa-chalkboard-teacher me-2"></i>Nuevo Profesor</a></li>
                        <li><a class="dropdown-item" href="cursos.php?action=new"><i class="fas fa-book me-2"></i>Nuevo Curso</a></li>
                        <li><a class="dropdown-item" href="salones.php?action=new"><i class="fas fa-chalkboard me-2"></i>Nuevo Salón</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-primary" href="reportes.php"><i class="fas fa-chart-line me-2"></i>Ver Reportes</a></li>
                    </ul>
                </li>
                
                <!-- Notificaciones -->
                <li class="nav-item dropdown me-3">
                    <a class="nav-link dropdown-toggle text-white position-relative" href="#" role="button" data-bs-toggle="dropdown" id="notificationsBtn">
                        <i class="fas fa-bell me-1"></i>
                        <?php if ($notificaciones['total'] > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                <?php echo $notificaciones['total'] > 99 ? '99+' : $notificaciones['total']; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end notifications-dropdown" style="width: 320px;">
                        <div class="notifications-header d-flex justify-content-between align-items-center p-3 border-bottom">
                            <h6 class="mb-0">Notificaciones</h6>
                            <?php if ($notificaciones['total'] > 0): ?>
                                <a href="#" class="text-primary small">Marcar todas como leídas</a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="notifications-body" style="max-height: 300px; overflow-y: auto;">
                            <?php if ($nuevas_solicitudes > 0): ?>
                                <div class="alert-message p-3 border-bottom">
                                    <div class="d-flex justify-content-between mb-1">
                                        <strong><span class="status-indicator status-new me-2"></span>Nuevas solicitudes</strong>
                                        <small class="text-muted">Hace 10 min</small>
                                    </div>
                                    <p class="mb-0 small">Tienes <?php echo $nuevas_solicitudes; ?> nuevas solicitudes de contacto</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="alert-message p-3 border-bottom">
                                <div class="d-flex justify-content-between mb-1">
                                    <strong><span class="status-indicator status-info me-2"></span>Sistema actualizado</strong>
                                    <small class="text-muted">Hace 2 horas</small>
                                </div>
                                <p class="mb-0 small">El sistema ha sido actualizado con nuevas funcionalidades</p>
                            </div>
                            
                            <?php if ($notificaciones['total'] == 0): ?>
                                <div class="text-center p-4">
                                    <i class="fas fa-bell-slash text-muted fa-2x mb-2"></i>
                                    <p class="text-muted mb-0">No tienes notificaciones pendientes</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="notifications-footer text-center p-2 border-top">
                            <a href="notificaciones.php" class="text-primary small text-decoration-none">
                                <i class="fas fa-bell me-1"></i>Ver todas las notificaciones
                            </a>
                        </div>
                    </div>
                </li>
                
                <!-- Perfil de usuario -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <?php if (!empty($admin['avatar_url'])): ?>
                            <img src="<?php echo $admin['avatar_url']; ?>" alt="Perfil" class="rounded-circle me-2" style="width: 32px; height: 32px;">
                        <?php else: ?>
                            <div class="rounded-circle bg-light text-primary me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 14px; font-weight: bold;">
                                <?php echo strtoupper(substr($admin['nombre'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <span class="d-none d-md-inline"><?php echo htmlspecialchars($admin['nombre']); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <div class="dropdown-item-text">
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($admin['avatar_url'])): ?>
                                        <img src="<?php echo $admin['avatar_url']; ?>" alt="Perfil" class="rounded-circle me-2" style="width: 42px; height: 42px;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-primary text-white me-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 42px;">
                                            <?php echo strtoupper(substr($admin['nombre'], 0, 1) . substr($admin['apellido'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo htmlspecialchars($admin['nombre'] . ' ' . $admin['apellido']); ?></strong>
                                        <div class="small text-muted"><?php echo htmlspecialchars($admin['email']); ?></div>
                                        <div class="small text-primary">Administrador</div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-user me-2"></i>Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="configuracion.php"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="ayuda.php"><i class="fas fa-question-circle me-2"></i>Ayuda</a></li>
                        <li><a class="dropdown-item text-danger" href="/ourcenter/server/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="content-container p-4">
        <!-- Alertas del sistema -->
        <div id="system-alerts"></div>
        
        <!-- Título de la página -->
        <?php if (isset($page_title)): ?>
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
                <h1 class="h2 mb-2"><?php echo $page_title; ?></h1>
                <?php if (isset($page_actions)): ?>
                    <div class="btn-toolbar mb-2">
                        <?php echo $page_actions; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Aquí va el contenido específico de cada página -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
    // Verificar si jQuery y Bootstrap están cargados
    console.log('jQuery loaded:', typeof jQuery !== 'undefined');
    console.log('Bootstrap loaded:', typeof bootstrap !== 'undefined');

    // Variables globales
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    // Crear overlay para móviles
    const sidebarOverlay = document.createElement('div');
    sidebarOverlay.className = 'sidebar-overlay';
    document.body.appendChild(sidebarOverlay);

    // Función para toggle del sidebar
    function toggleSidebar() {
        console.log('Toggling sidebar');
        sidebar.classList.toggle('show');
        body.classList.toggle('sidebar-hidden');
        if (window.innerWidth < 768) {
            sidebarOverlay.classList.toggle('active');
        }
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('sidebarToggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleSidebar);
    }
    });

    sidebarOverlay.addEventListener('click', () => {
        if (sidebar.classList.contains('show') && window.innerWidth < 768) {
            toggleSidebar();
        }
    });

    // Función para ajustar según el tamaño de pantalla
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

    // Manejar el toggle para desktop
    document.getElementById('sidebarToggle').addEventListener('click', () => {
        if (window.innerWidth >= 768) {
            if (sidebar.classList.contains('show')) {
                body.classList.remove('sidebar-hidden-by-user');
            } else {
                body.classList.add('sidebar-hidden-by-user');
            }
        }
    });

    // Event listeners para responsive
    window.addEventListener('load', adjustForScreenSize);
    window.addEventListener('resize', adjustForScreenSize);

    // Cerrar notificaciones al hacer clic fuera
    document.addEventListener('click', (event) => {
        const notificationsDropdown = document.querySelector('.notifications-dropdown');
        const notificationsBtn = document.getElementById('notificationsBtn');

        if (notificationsDropdown && !notificationsDropdown.contains(event.target) && event.target !== notificationsBtn) {
            // Cerrar dropdown si está abierto
            const dropdownInstance = bootstrap.Dropdown.getInstance(notificationsBtn);
            if (dropdownInstance) {
                dropdownInstance.hide();
            }
        }
    });

    function marcarNotificacionesComoLeidas() {
        console.log('Marking notifications as read');
        fetch('../../server/marcar_notificaciones_leidas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                usuario_id: <?php echo $admin_id; ?>
            })
        })
        .then(response => {
            console.log('Response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            if (data.success) {
                const badge = document.querySelector('#notificationsBtn .badge');
                if (badge) {
                    badge.style.display = 'none';
                }
                toastr.success('Notificaciones marcadas como leídas');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Error al marcar notificaciones');
        });
    }

    // Event listener para marcar como leídas
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM fully loaded and parsed');

        // Inicializar manualmente los menús desplegables
        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        dropdownElementList.map(function(dropdownToggleEl) {
            console.log('Initializing dropdown for:', dropdownToggleEl);
            return new bootstrap.Dropdown(dropdownToggleEl);
        });

        // Añadir listeners para depurar
        document.querySelectorAll('.dropdown-toggle').forEach(function(dropdown) {
            dropdown.addEventListener('click', function() {
                console.log('Dropdown clicked:', this);
            });
        });

        const links = document.querySelectorAll('a[href="#"]');
        const marcarLeidasBtn = Array.from(links).find(link => link.textContent.trim() === "Marcar todas como leídas");

        if (marcarLeidasBtn) {
            marcarLeidasBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Mark all as read clicked');
                marcarNotificacionesComoLeidas();
            });
        }
    });
</script>
        <style>
            /* Estilos base del sistema */
            body {
                font-family: 'Poppins', sans-serif;
                background-color: #f8f9fa;
                color: #333;
            }

            /* Sidebar Styles */
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                width: 250px;
                background-color: #fff;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                z-index: 1000;
                transition: all 0.3s;
            }

            .sidebar-menu {
                padding: 1rem 0;
                overflow-y: auto;
            }

            .sidebar-menu .nav-link {
                display: flex;
                align-items: center;
                padding: 0.75rem 1rem;
                color: #495057;
                text-decoration: none;
                transition: all 0.3s;
            }

            .sidebar-menu .nav-link:hover {
                background-color: #f8f9fa;
                color: #0a1b5c;
            }

            .sidebar-menu .nav-link.active {
                background-color: #e3f2fd;
                color: #0a1b5c;
                border-right: 3px solid #0a1b5c;
            }

            .sidebar-menu .nav-link i {
                margin-right: 0.75rem;
                font-size: 1rem;
                width: 20px;
                text-align: center;
            }

            .sidebar-menu .nav-header {
                padding: 0.75rem 1rem;
                font-size: 0.75rem;
                color: #6c757d;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                font-weight: 600;
                margin-top: 1rem;
            }

            .sidebar-menu .nav-header:first-child {
                margin-top: 0;
            }

            /* Main Content Styles */
            .main-content {
                margin-left: 250px;
                transition: all 0.3s;
                min-height: 100vh;
            }

            .content-container {
                padding: 1.5rem;
            }

            /* Navbar personalizado */
            .navbar {
                background-color: #0a1b5c !important;
            }

            /* Status indicators */
            .status-indicator {
                display: inline-block;
                width: 8px;
                height: 8px;
                border-radius: 50%;
            }

            .status-new {
                background-color: #dc3545;
            }

            .status-info {
                background-color: #0dcaf0;
            }

            .status-process {
                background-color: #ffc107;
            }

            /* Dropdown de notificaciones */
            .notifications-dropdown {
                max-width: 320px;
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            }

            .alert-message {
                transition: background-color 0.2s;
            }

            .alert-message:hover {
                background-color: #f8f9fa;
            }

            /* Badge styles */
            .badge {
                font-size: 0.75rem;
                padding: 0.25em 0.5em;
            }

            /* Overlay para móviles */
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 999;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s;
            }

            .sidebar-overlay.active {
                opacity: 1;
                visibility: visible;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .sidebar {
                    transform: translateX(-100%);
                }

                .sidebar.show {
                    transform: translateX(0);
                }

                .main-content {
                    margin-left: 0;
                }

                .sidebar-menu .nav-link span {
                    display: inline;
                }
            }

            /* Ajustes para cuando el sidebar está oculto */
            .sidebar-hidden .main-content {
                margin-left: 0;
            }

            /* Botón toggle */
            #sidebar-toggle {
                border: none;
                background: none;
                color: white;
            }

            #sidebar-toggle:hover {
                color: #ccc;
            }

            /* Scrollbar personalizado para el sidebar */
            .sidebar-menu::-webkit-scrollbar {
                width: 4px;
            }

            .sidebar-menu::-webkit-scrollbar-track {
                background: #f1f1f1;
            }
            /* Mejoras en el diseño responsive */
@media (max-width: 576px) {
    .content-container {
        padding: 1rem;
    }
    
    .page-title {
        font-size: 1.25rem;
    }
    
    .navbar {
        padding: 0.5rem 1rem !important;
    }
    
    .sidebar-menu .nav-link {
        padding: 0.5rem 1rem;
    }
    
    .notifications-dropdown {
        width: 280px !important;
        margin-right: -20px;
    }
}

/* Animaciones suaves */
.nav-link {
    transition: all 0.2s ease-in-out;
}

.badge {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

/* Indicador de carga */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #0a1b5c;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
        </style>