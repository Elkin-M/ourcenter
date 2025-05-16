<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');

// Conexión PDO ($pdo)
require_once '../config/db.php'; // ajusta este path

// Cargar datos de resumen
$total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$total_cursos = $pdo->query("SELECT COUNT(*) FROM cursos")->fetchColumn();
$total_inscripciones = $pdo->query("SELECT COUNT(*) FROM inscripciones")->fetchColumn();
$nuevas_solicitudes = $pdo->query("SELECT COUNT(*) FROM solicitudes_contacto WHERE estado = 'nuevo'")->fetchColumn();

// Cargar distribución de pagos
$stmt = $pdo->query("SELECT estado, COUNT(*) AS cantidad FROM pagos GROUP BY estado");
$pagos_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // ['completado' => 10, 'pendiente' => 5, ...]
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Our Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Favicon básico -->
    <link rel="icon" href="../images/favicon/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" sizes="180x180" href="../images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../images/favicon/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="../images/favicon/android-chrome-512x512.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../images/favicon/favicon-16x16.png">
    <link rel="manifest" href="../images/favicon/site.webmanifest">
    <meta name="theme-color" content="#0a1b5c">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/mstile-144x144.png">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom styles -->
    
</head>
<body>
    <!-- Preloader (mantenido del original) -->
    <?php include 'preloader.php';?>
    
    <!-- Sidebar Toggle Button -->
    <div class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Sidebar Navigation -->
    <div class="sidebar" id="sidebar">
        <div class="logo-container">
            <div class="logo">
                <img src="../images/logo.webp" alt="Logo de Our Center">
                <div class="logo-text" style="
    margin-left: 10px;">OUR CENTER</div>
                <!-- <div class="logo-short">OC</div> -->
            </div>
        </div>
        
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./admin/Usuarios.php">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Usuarios</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./admin/cursos.php">
                    <i class="fas fa-book"></i>
                    <span class="nav-text">Cursos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./admin/inscripciones.php">
                    <i class="fas fa-user-graduate"></i>
                    <span class="nav-text">Inscripciones</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./admin/pagos.php">
                    <i class="fas fa-credit-card"></i>
                    <span class="nav-text">Pagos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./admin/solicitudes.php">
                    <i class="fas fa-envelope"></i>
                    <span class="nav-text">Solicitudes</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./admin/reportes.php">
                    <i class="fas fa-chart-bar"></i>
                    <span class="nav-text">Reportes</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./admin/configuracion.php">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">Configuración</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header con título y acciones -->
        <div class="dashboard-header">
            <div>
                <h1 class="page-title" style="color: white;">Dashboard</h1>
            </div>
            <div class="d-flex align-items-center">
                <!-- Notificaciones -->
                <div class="notifications me-4">
                    <i class="fas fa-bell notifications-icon" style="color: white;" id="notificationsBtn"></i>
                    <span class="notifications-badge"><?= $nuevas_solicitudes ?></span>
                </div>
                
                <!-- User dropdown -->
                <div class="user-dropdown dropdown">
                    <button class="dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="../images/2.jpg" alt="Usuario">
<span><?= isset($_SESSION['usuario_nombre']) ? htmlspecialchars($_SESSION['usuario_nombre']) : 'Administrador'; ?></span>                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li class="user-header">
                            <div class="user-name"><?= isset($_SESSION['usuario_nombre']) ? htmlspecialchars($_SESSION['usuario_nombre']) : 'Usuario'; ?></div>
                            <div class="user-role">Administrador</div>
                        </li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user"></i> Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog"></i> Configuración</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
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
        
        <!-- Tarjetas de resumen -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary-color h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title">Usuarios</h5>
                                <p class="card-text fs-2"><?= $total_usuarios ?></p>
                            </div>
                            <div>
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-white-50">5 nuevos esta semana</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-secondary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title">Cursos</h5>
                                <p class="card-text fs-2"><?= $total_cursos ?></p>
                            </div>
                            <div>
                                <i class="fas fa-book fa-2x"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-white-50">3 cursos activos</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title">Inscripciones</h5>
                                <p class="card-text fs-2"><?= $total_inscripciones ?></p>
                            </div>
                            <div>
                                <i class="fas fa-user-graduate fa-2x"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-white-50">12 nuevas este mes</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title">Solicitudes nuevas</h5>
                                <p class="card-text fs-2"><?= $nuevas_solicitudes ?></p>
                            </div>
                            <div>
                                <i class="fas fa-envelope fa-2x"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-dark">Pendientes de atención</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Acciones rápidas -->
        <div class="quick-actions mb-4">
            <h4 class="section-title">Acciones Rápidas</h4>
            <div class="row g-3">
                <div class="col-md-3 col-sm-6">
                    <div class="action-btn">
                        <i class="fas fa-user-plus"></i>
                        <span>Nuevo Usuario</span>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="action-btn">
                        <i class="fas fa-book-medical"></i>
                        <span>Nuevo Curso</span>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="action-btn">
                        <i class="fas fa-user-graduate"></i>
                        <span>Nueva Inscripción</span>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="action-btn">
                        <i class="fas fa-chart-line"></i>
                        <span>Reportes</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <!-- Gráfico de resumen -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Resumen General</span>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="chartFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Este mes
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="chartFilterDropdown">
                                <li><a class="dropdown-item" href="#">Este mes</a></li>
                                <li><a class="dropdown-item" href="#">Último trimestre</a></li>
                                <li><a class="dropdown-item" href="#">Último año</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="resumenChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gráfico de pagos -->
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header">Distribución de Pagos</div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="pagosChart"></canvas>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Completados</span>
                                <span class="badge bg-success"><?= $pagos_data['completado'] ?? 0 ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Pendientes</span>
                                <span class="badge bg-warning"><?= $pagos_data['pendiente'] ?? 0 ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Fallidos</span>
                                <span class="badge bg-danger"><?= $pagos_data['fallido'] ?? 0 ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Tabla de solicitudes recientes -->
            <div class="col-md-6">
                <div class="recent-items">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="section-title mb-0">Solicitudes Recientes</h4>
                        <a href="solicitudes.php" class="btn btn-sm btn-outline-primary">Ver todas</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Curso</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>María González</td>
                                    <td>Teen English</td>
                                    <td><span class="badge bg-danger">Nuevo</span></td>
                                    <td>05/05/2025</td>
                                </tr>
                                <tr>
                                    <td>Juan Pérez</td>
                                    <td>Kids English</td>
                                    <td><span class="badge bg-warning">En proceso</span></td>
                                    <td>03/05/2025</td>
                                </tr>
                                <tr>
                                    <td>Ana Martínez</td>
                                    <td>Professional English</td>
                                    <td><span class="badge bg-success">Completado</span></td>
                                    <td>01/05/2025</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de inscripciones recientes -->
            <div class="col-md-6">
                <div class="recent-items">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="section-title mb-0">Inscripciones Recientes</h4>
                        <a href="inscripciones.php" class="btn btn-sm btn-outline-primary">Ver todas</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Estudiante</th>
                                    <th>Curso</th>
                                    <th>Estado</th>
                                    <th>Pago</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Luis Ramírez</td>
                                    <td>Professional English</td>
                                    <td><span class="badge bg-success">Activo</span></td>
                                    <td><span class="badge bg-success">Pagado</span></td>
                                </tr>
                                <tr>
                                    <td>Carmen López</td>
                                    <td>Kids English</td>
                                    <td><span class="badge bg-success">Activo</span></td>
                                    <td><span class="badge bg-warning">Pendiente</span></td>
                                </tr>
                                <tr>
                                    <td>Pedro Sánchez</td>
                                    <td>Teen English</td>
                                    <td><span class="badge bg-danger">Inactivo</span></td>
                                    <td><span class="badge bg-danger">Vencido</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Calendario de eventos y próximas clases -->
        <div class="row mb-4">
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Próximas Clases</span>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="calendarFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Esta semana
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="calendarFilterDropdown">
                                <li><a class="dropdown-item" href="#">Esta semana</a></li>
                                <li><a class="dropdown-item" href="#">Próxima semana</a></li>
                                <li><a class="dropdown-item" href="#">Este mes</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Curso</th>
                                        <th>Profesor</th>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Aula</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Professional English</td>
                                        <td>Laura Jiménez</td>
                                        <td>06/05/2025</td>
                                        <td>10:00 - 12:00</td>
                                        <td>Aula 3</td>
                                    </tr>
                                    <tr>
                                        <td>Kids English</td>
                                        <td>Roberto Fernández</td>
                                        <td>06/05/2025</td>
                                        <td>16:00 - 17:30</td>
                                        <td>Aula 1</td>
                                    </tr>
                                    <tr>
                                        <td>Teen English</td>
                                        <td>María Torres</td>
                                        <td>07/05/2025</td>
                                        <td>15:00 - 17:00</td>
                                        <td>Aula 2</td>
                                    </tr>
                                    <tr>
                                        <td>Business English</td>
                                        <td>Carlos Vega</td>
                                        <td>08/05/2025</td>
                                        <td>18:00 - 20:00</td>
                                        <td>Aula 4</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Últimos pagos -->
            <div class="col-md-5">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Últimos Pagos</span>
                        <a href="pagos.php" class="btn btn-sm btn-outline-primary">Ver todos</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Estudiante</th>
                                        <th>Monto</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Luis Ramírez</td>
                                        <td>$350.00</td>
                                        <td>05/05/2025</td>
                                        <td><span class="badge bg-success">Completado</span></td>
                                    </tr>
                                    <tr>
                                        <td>Ana Martínez</td>
                                        <td>$275.00</td>
                                        <td>04/05/2025</td>
                                        <td><span class="badge bg-success">Completado</span></td>
                                    </tr>
                                    <tr>
                                        <td>Carlos Pérez</td>
                                        <td>$350.00</td>
                                        <td>03/05/2025</td>
                                        <td><span class="badge bg-warning">Pendiente</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Alertas de sistema y tareas pendientes -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Alertas del Sistema</div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                    <span>5 pagos pendientes próximos a vencer</span>
                                </div>
                                <a href="#" class="btn btn-sm btn-outline-primary">Ver</a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <i class="fas fa-user-check text-success me-2"></i>
                                    <span>3 nuevos usuarios requieren activación</span>
                                </div>
                                <a href="#" class="btn btn-sm btn-outline-primary">Revisar</a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <i class="fas fa-calendar-check text-info me-2"></i>
                                    <span>Próximo inicio de curso: Teen English (10/05)</span>
                                </div>
                                <a href="#" class="btn btn-sm btn-outline-primary">Detalles</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Tareas Pendientes</div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex align-items-center p-3">
                                <div class="form-check flex-grow-1">
                                    <input class="form-check-input" type="checkbox" value="" id="task1">
                                    <label class="form-check-label" for="task1">
                                        Confirmar inscripciones del nuevo curso
                                    </label>
                                </div>
                                <span class="badge bg-warning">Alta</span>
                            </li>
                            <li class="list-group-item d-flex align-items-center p-3">
                                <div class="form-check flex-grow-1">
                                    <input class="form-check-input" type="checkbox" value="" id="task2">
                                    <label class="form-check-label" for="task2">
                                        Enviar recordatorios de pago
                                    </label>
                                </div>
                                <span class="badge bg-info">Media</span>
                            </li>
                            <li class="list-group-item d-flex align-items-center p-3">
                                <div class="form-check flex-grow-1">
                                    <input class="form-check-input" type="checkbox" value="" id="task3">
                                    <label class="form-check-label" for="task3">
                                        Actualizar material del curso Professional English
                                    </label>
                                </div>
                                <span class="badge bg-secondary">Baja</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS y Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Scripts personalizados -->
    <script>
       
// Elementos del DOM
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
const sidebarToggle = document.getElementById('sidebarToggle');
const body = document.body;

// Crear overlay para dispositivos móviles
const sidebarOverlay = document.createElement('div');
sidebarOverlay.className = 'sidebar-overlay';
document.body.appendChild(sidebarOverlay);

// Función para cambiar el estado del sidebar
function toggleSidebar() {
    sidebar.classList.toggle('show');
    body.classList.toggle('sidebar-hidden');
    
    // En dispositivos móviles, mostrar/ocultar overlay
    if (window.innerWidth < 768) {
        sidebarOverlay.classList.toggle('active');
    }
}

// Event listeners
sidebarToggle.addEventListener('click', toggleSidebar);

// Cerrar sidebar al hacer clic en el overlay (en móvil)
sidebarOverlay.addEventListener('click', function() {
    if (sidebar.classList.contains('show') && window.innerWidth < 768) {
        toggleSidebar();
    }
});

// Función para ajustar la interfaz según el tamaño de la pantalla
function adjustForScreenSize() {
    if (window.innerWidth < 768) {
        sidebar.classList.remove('show');
        body.classList.add('sidebar-hidden');
        mainContent.style.marginLeft = '0';
    } else {
        // En pantallas grandes, mostrar sidebar por defecto
        // a menos que el usuario haya elegido ocultarlo previamente
        if (!body.classList.contains('sidebar-hidden-by-user')) {
            sidebar.classList.add('show');
            body.classList.remove('sidebar-hidden');
        }
    }
}

// Guardar preferencia del usuario al ocultar sidebar manualmente en pantallas grandes
sidebarToggle.addEventListener('click', function() {
    if (window.innerWidth >= 768) {
        if (sidebar.classList.contains('show')) {
            body.classList.remove('sidebar-hidden-by-user');
        } else {
            body.classList.add('sidebar-hidden-by-user');
        }
    }
});

// Verificar el tamaño de la pantalla al cargar y redimensionar
window.addEventListener('load', adjustForScreenSize);
window.addEventListener('resize', adjustForScreenSize);
        
        
        // Notificaciones
        const notificationsBtn = document.getElementById('notificationsBtn');
        const alertBox = document.getElementById('alertBox');
        const closeAlertBox = document.getElementById('closeAlertBox');
        
        notificationsBtn.addEventListener('click', function() {
            alertBox.classList.toggle('show');
        });
        
        closeAlertBox.addEventListener('click', function() {
            alertBox.classList.remove('show');
        });
        
        // Detectar clic fuera del alertBox para cerrarlo
        document.addEventListener('click', function(event) {
            if (!alertBox.contains(event.target) && event.target !== notificationsBtn) {
                alertBox.classList.remove('show');
            }
        });

        // Gráficos
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de resumen general
            const resumenCtx = document.getElementById('resumenChart').getContext('2d');
            const resumenChart = new Chart(resumenCtx, {
                type: 'line',
                data: {
                    labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo'],
                    datasets: [{
                        label: 'Inscripciones',
                        data: [12, 19, 10, 15, 22],
                        backgroundColor: 'rgba(10, 27, 92, 0.2)',
                        borderColor: 'rgba(10, 27, 92, 1)',
                        borderWidth: 2,
                        tension: 0.3
                    }, {
                        label: 'Ingresos (x100€)',
                        data: [30, 45, 22, 37, 48],
                        backgroundColor: 'rgba(231, 76, 60, 0.1)',
                        borderColor: 'rgba(231, 76, 60, 1)',
                        borderWidth: 2,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });
            
            // Gráfico de pagos
            const pagosCtx = document.getElementById('pagosChart').getContext('2d');
            const pagosChart = new Chart(pagosCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Completados', 'Pendientes', 'Fallidos'],
                    datasets: [{
                        data: [75, 20, 5],
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(255, 193, 7, 0.8)',
                            'rgba(220, 53, 69, 0.8)'
                        ],
                        borderColor: [
                            'rgba(40, 167, 69, 1)',
                            'rgba(255, 193, 7, 1)',
                            'rgba(220, 53, 69, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>