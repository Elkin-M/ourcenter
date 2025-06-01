<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');


// Verificar que el usuario está logueado y es profesor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] !== 3) {
    header('Location: ../../login.php?redirect=teacher');
    exit();
}

require_once '../../config/db.php';

// Obtener información del profesor
$teacher_id = $_SESSION['usuario_id'];

$query = "SELECT u.*, r.nombre as rol_nombre 
          FROM usuarios u 
          JOIN roles r ON u.rol_id = r.id
          WHERE u.id = ? AND u.rol_id = 3";
$stmt = $pdo->prepare($query);
$stmt->execute([$teacher_id]);
$teacher = $stmt->fetch();

if (!$teacher) {
    session_destroy();
    header('Location: ../../login.php');
    exit();
}

// Obtener estadísticas del profesor por salones
$stats_query = "SELECT 
    COUNT(DISTINCT s.id) as total_salones,
    COUNT(DISTINCT CASE WHEN s.estado = 'activo' THEN s.id END) as salones_activos,
    SUM(s.capacidad) as capacidad_total,
    COUNT(DISTINCT u.id) as total_estudiantes
FROM salones s
LEFT JOIN usuarios u ON u.salon_id = s.id AND u.rol_id = 3 -- 3 = estudiante
WHERE s.teacher_id = ?";

$stats_stmt = $pdo->prepare($stats_query);
$stats_stmt->execute([$teacher_id]);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Obtener lista de salones asignados al profesor
$salones_query = "SELECT 
    s.id, 
    s.nombre, 
    s.codigo,
    c.nombre as curso_nombre,
    CONCAT('Nivel ', s.nivel) as nivel,
    COUNT(DISTINCT u.id) as num_estudiantes,
    s.capacidad,
    s.estado
FROM salones s
LEFT JOIN cursos c ON s.curso_id = c.id
LEFT JOIN usuarios u ON u.salon_id = s.id AND u.rol_id = 3
WHERE s.teacher_id = ?
GROUP BY s.id
ORDER BY c.nombre, s.nivel";

$salones_stmt = $pdo->prepare($salones_query);
$salones_stmt->execute([$teacher_id]);
$salones = $salones_stmt->fetchAll();

// Obtener notificaciones pendientes
$notificaciones_query = "SELECT COUNT(*) as total FROM notificaciones 
                         WHERE usuario_destino_id = ? AND leida = 0";
$notif_stmt = $pdo->prepare($notificaciones_query);
$notif_stmt->execute([$teacher_id]);
$notificaciones = $notif_stmt->fetch();

// Obtener página actual
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>OurCenter | Panel de Profesor</title>
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

    <!-- CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/ourcenter-teacher.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
<div class="sidebar bg-white shadow-sm d-flex flex-column">
    <!-- <div class="sidebar-header p-3 border-bottom text-center">
        <img src="assets/img/logo.png" alt="OurCenter" class="img-fluid sidebar-logo mb-2" style="max-height: 40px;">
        <h6 class="text-primary mb-0">Panel de Profesor</h6>
    </div> -->
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
        <div class="corporation-info">
            <h6 class="mb-0 text-truncate"><?php echo htmlspecialchars($corporation_name); ?></h6>
        </div>
    </div>
</div>

    
    <div class="sidebar-menu flex-grow-1 overflow-auto">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'teacher_dashboard' ? 'active' : ''; ?>" href="teacher_dashboard.php">
                    <i class="fas fa-home me-2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="nav-header">GESTIÓN DE SALONES</li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'teacher_salones' ? 'active' : ''; ?>" href="teacher_salones.php">
                    <i class="fas fa-chalkboard me-2"></i>
                    <span>Mis Salones</span>
                    <span class="badge rounded-pill bg-primary ms-auto"><?php echo $stats['salones_activos']; ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo in_array($current_page, ['teacher_horarios', 'teacher_schedule']) ? 'active' : ''; ?>" href="teacher_horarios.php">
                    <i class="fas fa-calendar-alt me-2"></i>
                    <span>Horarios</span>
                </a>
            </li>
            
            <li class="nav-header">ESTUDIANTES</li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'teacher_estudiantes' ? 'active' : ''; ?>" href="teacher_estudiantes.php">
                    <i class="fas fa-users me-2"></i>
                    <span>Estudiantes</span>
                    <span class="badge rounded-pill bg-info ms-auto"><?php echo $stats['total_estudiantes']; ?></span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'teacher_asistencia' ? 'active' : ''; ?>" href="teacher_asistencia.php">
                    <i class="fas fa-clipboard-check me-2"></i>
                    <span>Control de Asistencia</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'teacher_evaluaciones' ? 'active' : ''; ?>" href="teacher_evaluaciones.php">
                    <i class="fas fa-star me-2"></i>
                    <span>Evaluaciones</span>
                </a>
            </li>
            
            <li class="nav-header">RECURSOS</li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'teacher_materiales' ? 'active' : ''; ?>" href="teacher_materiales.php">
                    <i class="fas fa-book me-2"></i>
                    <span>Materiales</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'teacher_actividades' ? 'active' : ''; ?>" href="teacher_actividades.php">
                    <i class="fas fa-tasks me-2"></i>
                    <span>Actividades</span>
                </a>
            </li>
            
            <li class="nav-header">SISTEMA</li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'teacher_reportes' ? 'active' : ''; ?>" href="teacher_reportes.php">
                    <i class="fas fa-chart-bar me-2"></i>
                    <span>Reportes</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'teacher_profile' ? 'active' : ''; ?>" href="teacher_profile.php">
                    <i class="fas fa-user-cog me-2"></i>
                    <span>Mi Perfil</span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="sidebar-footer p-3 border-top">
        <a href="logout.php" class="btn btn-outline-secondary btn-sm d-block">
            <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
        </a>
    </div>
</div>

<!-- Contenido principal -->
<div class="main-content">
    <!-- Navbar superior -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white py-2 px-3 shadow-sm" style="padding: 28px !important;">
        <div class="container-fluid px-0">
            <!-- Botón para mostrar/ocultar sidebar en móviles -->
            <button id="sidebar-toggle" class="btn btn-sm btn-link text-secondary d-lg-none me-2">
                <i class="fas fa-bars fa-lg"></i>
            </button>
            
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="teacher_dashboard.php">Inicio</a></li>
                    <?php if (isset($breadcrumb)): ?>
                        <?php foreach ($breadcrumb as $item): ?>
                            <?php if (isset($item['url'])): ?>
                                <li class="breadcrumb-item"><a href="<?php echo $item['url']; ?>"><?php echo $item['title']; ?></a></li>
                            <?php else: ?>
                                <li class="breadcrumb-item active"><?php echo $item['title']; ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ol>
            </nav>
            
            <!-- Componentes del navbar derecho -->
            <ul class="navbar-nav ms-auto" style="display: ruby;">
                <!-- Selector de salón -->
                <li class="nav-item dropdown me-2">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-chalkboard me-1"></i>
                        <span class="d-none d-md-inline">Salones</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Mis Salones</h6></li>
                        
                        <?php if (empty($salones)): ?>
                            <li><span class="dropdown-item-text text-muted">No tienes salones asignados</span></li>
                        <?php else: ?>
                            <?php foreach ($salones as $salon): ?>
                                <li>
                                    <a class="dropdown-item d-flex justify-content-between align-items-center" 
                                       href="teacher_salon_detail.php?id=<?php echo $salon['id']; ?>">
                                        <div>
                                            <strong><?php echo htmlspecialchars($salon['nombre']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($salon['curso_nombre'] . ' - ' . $salon['nivel']); ?>
                                            </small>
                                        </div>
                                        <span class="badge rounded-pill <?php echo $salon['estado'] == 'activo' ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo $salon['num_estudiantes']; ?>/<?php echo $salon['capacidad']; ?>
                                        </span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-primary" href="teacher_salones.php">
                                    <i class="fas fa-list me-1"></i>Ver todos los salones
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
                
                <!-- Notificaciones -->
                <li class="nav-item dropdown me-2">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell me-1"></i>
                        <?php if ($notificaciones['total'] > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $notificaciones['total']; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end notifications-dropdown">
                        <div class="notifications-header d-flex justify-content-between align-items-center p-3">
                            <h6 class="mb-0">Notificaciones</h6>
                            <?php if ($notificaciones['total'] > 0): ?>
                                <a href="teacher_mark_read_notifications.php" class="text-primary small">Marcar todas como leídas</a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="notifications-body" id="notifications-container">
                            <div class="text-center p-3">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mb-0 mt-2 text-muted small">Cargando notificaciones...</p>
                            </div>
                        </div>
                        
                        <div class="notifications-footer text-center p-2 border-top">
                            <a href="teacher_notificaciones.php" class="text-primary">Ver todas</a>
                        </div>
                    </div>
                </li>
                
                <!-- Perfil de usuario -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" style="display: ruby;" href="#" role="button" data-bs-toggle="dropdown">
                        <?php if (!empty($teacher['avatar_url'])): ?>
                            <img src="<?php echo $teacher['avatar_url']; ?>" alt="Perfil" class="rounded-circle header-avatar">
                        <?php else: ?>
                            <div class="header-avatar-placeholder rounded-circle bg-primary text-white">
                                <?php echo strtoupper(substr($teacher['nombre'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <span class="d-none d-md-inline ms-1"><?php echo htmlspecialchars($teacher['nombre']); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <div class="dropdown-item-text">
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($teacher['avatar_url'])): ?>
                                        <img src="<?php echo $teacher['avatar_url']; ?>" alt="Perfil" class="rounded-circle me-2" style="width: 42px; height: 42px;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-primary text-white me-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                            <?php echo strtoupper(substr($teacher['nombre'], 0, 1) . substr($teacher['apellido'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo htmlspecialchars($teacher['nombre'] . ' ' . $teacher['apellido']); ?></strong>
                                        <div class="small text-muted"><?php echo htmlspecialchars($teacher['email']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="teacher_profile.php"><i class="fas fa-user me-2"></i>Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="teacher_configuracion.php"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="teacher_ayuda.php"><i class="fas fa-question-circle me-2"></i>Ayuda</a></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
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
        <style>
            /* General Styles */
body {
    font-family: 'Poppins', sans-serif;
    background-color: #f8f9fa;
    color: #333;
}
.navbar{
    background-color: #0a1b5c !important;
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

.sidebar-header {
    padding: 1rem;
    text-align: center;
    border-bottom: 1px solid #e9ecef;
}

.sidebar-logo {
    max-height: 40px;
}

.sidebar-menu {
    padding: 1rem 0;
    overflow-y: auto;
}

.sidebar-menu .nav {
    flex-direction: column;
}

.sidebar-menu .nav-item {
    position: relative;
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
}

.sidebar-menu .nav-link.active {
    background-color: #e9ecef;
    color: #0d6efd;
}

.sidebar-menu .nav-link i {
    margin-right: 0.75rem;
    font-size: 1rem;
}

.sidebar-menu .nav-header {
    padding: 0.75rem 1rem;
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid #e9ecef;
}

.sidebar-footer .btn {
    width: 100%;
}

/* Main Content Styles */
.main-content {
    margin-left: 250px;
    transition: all 0.3s;
}

.content-container {
    padding: 1.5rem;
}

/* Navbar Styles */
.navbar {
    background-color: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.navbar-nav {
    align-items: center;
}

.navbar-nav .nav-item {
    margin-left: 0.5rem;
}

.navbar-nav .nav-link {
    position: relative;
    padding: 0.5rem 0.75rem;
    color: #495057;
}

.navbar-nav .nav-link:hover {
    color: #0d6efd;
}

.navbar-nav .nav-link i {
    font-size: 1.25rem;
}

.navbar-nav .nav-link .badge {
    position: absolute;
    top: 0.25rem;
    right: 0.25rem;
}

/* Teacher Avatar Styles */
.teacher-avatar img,
.avatar-placeholder,
.header-avatar,
.header-avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-placeholder,
.header-avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #0d6efd;
    color: #fff;
    font-weight: bold;
}

.header-avatar {
    width: 30px;
    height: 30px;
}

/* Dropdown Styles */
.dropdown-menu {
    min-width: 12rem;
    padding: 0.5rem 0;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.dropdown-item {
    padding: 0.5rem 1rem;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

.dropdown-item.active {
    background-color: #e9ecef;
    color: #0d6efd;
}

.dropdown-header {
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.dropdown-divider {
    margin: 0.5rem 0;
}

/* Notifications Dropdown Styles */
.notifications-dropdown {
    width: 300px;
}

.notifications-header {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e9ecef;
}

.notifications-body {
    max-height: 300px;
    overflow-y: auto;
}

.notifications-footer {
    padding: 0.75rem 1rem;
    border-top: 1px solid #e9ecef;
}

/* Breadcrumb Styles */
.breadcrumb {
    background-color: transparent;
    padding: 0;
    margin: 0;
}

.breadcrumb-item a {
    color: #6c757d;
}

.breadcrumb-item a:hover {
    color: #0d6efd;
}

.breadcrumb-item.active {
    color: #495057;
}

/* Button Styles */
.btn {
    border-radius: 0.25rem;
}

.btn-outline-secondary {
    color: #6c757d;
    border-color: #6c757d;
}

.btn-outline-secondary:hover {
    color: #fff;
    background-color: #6c757d;
    border-color: #6c757d;
}

/* Badge Styles */
.badge {
    border-radius: 10rem;
    padding: 0.25em 0.5em;
    font-size: 0.75rem;
    font-weight: bold;
}

/* Utility Classes */
.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Responsive Styles */
@media (max-width: 992px) {
    .sidebar {
        transform: translateX(-100%);
    }

    .sidebar.show {
        transform: translateX(0);
    }

    .main-content {
        margin-left: 0;
    }

    #sidebar-toggle {
        display: block;
    }
}

        </style>