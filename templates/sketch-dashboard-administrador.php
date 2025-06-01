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

// Datos para KPIs (simulados)
$incremento_usuarios = 8.5; // % de incremento respecto al mes anterior
$incremento_inscripciones = 12.3;
$incremento_ingresos = 15.7;
$tasa_conversion = 64.2; // % de solicitudes que se convierten en inscripciones
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Our Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    <!-- Fullcalendar -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom styles -->
    <style>
        :root {
            --primary-color: #0a1b5c;
            --secondary-color: #4a6bdb;
            --accent-color: #e74c3c;
            --light-bg: #f8f9fa;
            --dark-text: #343a40;
            --light-text: #f8f9fa;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark-text);
        }
        
        /* Sidebar Styling - NO MODIFICAR */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100%;
            width: 260px;
            background-color: var(--primary-color);
            color: white;
            z-index: 1000;
            transition: all 0.3s;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }
        
        .sidebar-hidden .sidebar {
            left: -260px;
        }
        
        .sidebar-toggle {
            position: fixed;
            top: 20px;
            left: 20px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1001;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
        }
        
        .sidebar-hidden .sidebar-toggle {
            left: 20px;
        }
        
        .logo-container {
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo img {
            height: 40px;
            width: auto;
        }
        
        .logo-text {
            margin-left: 10px;
            font-size: 18px;
            font-weight: 600;
            color: white;
        }
        
        .nav-item {
            margin: 5px 0;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 5px;
            margin: 0 10px;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 20px;
            transition: all 0.3s;
            min-height: 100vh;
        }
        
        .sidebar-hidden .main-content {
            margin-left: 0;
        }
        
        /* NUEVO DISEÑO Y ESTILOS */
        
        /* Header */
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 15px;
            padding: 20px 30px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: rgba(255, 255, 255, 0.05);
            transform: rotate(30deg);
            pointer-events: none;
        }
        
        .page-title {
            font-weight: 700;
            font-size: 1.8rem;
            margin: 0;
            color: white;
            position: relative;
        }
        
        .dashboard-date {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 5px;
        }
        
        /* User Profile & Notifications */
        .user-dropdown {
            position: relative;
        }
        
        .dropdown-toggle {
            background: transparent;
            border: none;
            color: white;
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .dropdown-toggle img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            border: 2px solid rgba(255, 255, 255, 0.8);
        }
        
        .dropdown-menu {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .user-header {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 3px;
        }
        
        .user-role {
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        .dropdown-item {
            padding: 10px 15px;
        }
        
        .dropdown-item i {
            margin-right: 10px;
            width: 18px;
            text-align: center;
            color: var(--secondary-color);
        }
        
        /* Notifications */
        .notifications {
            position: relative;
        }
        
        .notifications-icon {
            font-size: 1.3rem;
            cursor: pointer;
        }
        
        .notifications-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--accent-color);
            color: white;
            font-size: 0.7rem;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .alert-box {
            position: absolute;
            top: 70px;
            right: 20px;
            width: 350px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            display: none;
            overflow: hidden;
            max-height: 80vh;
            transition: all 0.3s;
        }
        
        .alert-box.show {
            display: block;
        }
        
        .alert-header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
        }
        
        .alert-body {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .alert-message {
            border-bottom: 1px solid #eee;
            padding: 15px;
        }
        
        .alert-message:last-child {
            border-bottom: none;
        }
        
        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .status-new {
            background-color: var(--accent-color);
        }
        
        .status-process {
            background-color: var(--warning-color);
        }
        
        /* KPI Cards */
        .kpi-row {
            margin-bottom: 25px;
        }
        
        .kpi-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            height: 100%;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
            border: none;
        }
        
        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .kpi-card-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .kpi-card-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .kpi-card-warning {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
        }
        
        .kpi-card-info {
            background: linear-gradient(135deg, #17a2b8, #0dcaf0);
            color: white;
        }
        
        .kpi-card-body {
            padding: 20px;
            position: relative;
            z-index: 1;
        }
        
        .kpi-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2rem;
            opacity: 0.8;
        }
        
        .kpi-title {
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .kpi-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .kpi-trend {
            font-size: 0.85rem;
            display: flex;
            align-items: center;
        }
        
        .trend-up {
            color: #20c997;
        }
        
        .trend-down {
            color: #dc3545;
        }
        
        .kpi-trend i {
            margin-right: 5px;
        }
        
        /* Dashboard Main Sections */
        .dashboard-section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--primary-color);
            position: relative;
            padding-left: 15px;
        }
        
        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            height: 70%;
            width: 4px;
            background-color: var(--accent-color);
            border-radius: 5px;
        }
        
        /* Quick Actions */
        .quick-actions {
            margin-bottom: 25px;
        }
        
        .action-btn {
            background-color: white;
            border-radius: 12px;
            padding: 20px 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .action-btn i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--primary-color);
            transition: all 0.3s;
        }
        
        .action-btn:hover i {
            color: var(--accent-color);
        }
        
        .action-btn span {
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        /* Cards and Tables */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 15px 20px;
            font-weight: 600;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            font-weight: 600;
            color: var(--primary-color);
            border-top: none;
            border-bottom-width: 1px;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .badge {
            padding: 5px 10px;
            font-weight: 500;
            border-radius: 20px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .kpi-value {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                left: -260px;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar.show {
                left: 0;
            }
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .dashboard-header > div:last-child {
                margin-top: 15px;
                align-self: flex-end;
            }
            
            .chart-container {
                height: 250px;
            }
            
            .action-btn {
                margin-bottom: 15px;
            }
        }
        
        /* Nuevo calendario de clases */
        #classCalendar {
            height: 350px;
        }
        
        .fc-event {
            border: none;
            border-radius: 4px;
            padding: 3px 5px;
        }
        
        .fc-daygrid-day-number {
            font-weight: 500;
        }
        
        .fc-day-today {
            background-color: rgba(10, 27, 92, 0.05) !important;
        }
        
        /* Task list */
        .task-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .task-item:last-child {
            border-bottom: none;
        }
        
        .task-checkbox {
            margin-right: 15px;
        }
        
        .task-content {
            flex-grow: 1;
        }
        
        .task-priority {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .priority-high {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .priority-medium {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .priority-low {
            background-color: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }
        
        /* Preloader mantener */
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #fff;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Preloader (mantenido del original) -->
    <?php include 'preloader.php';?>
    
    <!-- Sidebar Toggle Button -->
    <div class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Sidebar Navigation - NO MODIFICAR -->
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
                <h1 class="page-title">Dashboard</h1>
                <div class="dashboard-date">
                    <i class="far fa-calendar-alt me-2"></i>
                    <?= date('l, d F Y') ?>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <!-- Buscador -->
                <div class="search-container me-4 d-none d-md-block">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" placeholder="Buscar..." style="border-radius: 20px 0 0 20px;">
                        <button class="btn btn-sm btn-light px-3" type="button" style="border-radius: 0 20px 20px 0;">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Notificaciones -->
                <div class="notifications me-4">
                    <i class="fas fa-bell notifications-icon" style="color: white;" id="notificationsBtn"></i>
                    <span class="notifications-badge"><?= $nuevas_solicitudes ?></span>
                </div>
                
                <!-- User dropdown -->
                <div class="user-dropdown dropdown">
                    <button class="dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="../images/2.jpg" alt="Usuario">
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
                <div class="alert-message">
                    <div class="d-flex justify-content-between mb-1">
                        <strong><span class="status-indicator status-new"></span> Nueva inscripción</strong>
                        <small class="text-muted">Hace 5 horas</small>
                    </div>
                    <p class="mb-0">Ana Martínez se ha inscrito en Professional English</p>
                </div>
            </div>
        </div>
        
        <!-- Panel de estadísticas principales (KPIs) -->
        <div class="row g-4 kpi-row">
            <div class="col-md-3 col-sm-6">
                <div class="kpi-card kpi-card-primary">
                    <div class="kpi-card-body">
                        <div class="kpi-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="kpi-title">Total de Usuarios</div>
                        <div class="kpi-value"><?= number_format($total_usuarios) ?></div>
                        <div class="kpi-trend">
                            <i class="fas fa-arrow-up trend-up"></i>
                            <span><?= number_format($incremento_usuarios, 1) ?>% vs. mes anterior</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="kpi-card kpi-card-success">
                    <div class="kpi-card-body">
                        <div class="kpi-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="kpi-title">Total de Cursos</div>
                        <div class="kpi-value"><?= number_format($total_cursos) ?></div>
                        <div class="kpi-trend">
                            <i class="fas fa-arrow-up trend-up"></i>
                            <span>2.5% vs. mes anterior</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="kpi-card kpi-card-warning">
                    <div class="kpi-card-body">
                        <div class="kpi-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="kpi-title">Inscripciones</div>
                        <div class="kpi-value"><?= number_format($total_inscripciones) ?></div>
                        <div class="kpi-trend">
                            <i class="fas fa-arrow-up trend-up"></i>
                            <span><?= number_format($incremento_inscripciones, 1) ?>% vs. mes anterior</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="kpi-card kpi-card-info">
                    <div class="kpi-card-body">
                        <div class="kpi-icon">
                            <i class="fas fa-euro-sign"></i>
                        </div>
                        <div class="kpi-title">Ingresos Mensuales</div>
                        <div class="kpi-value">€<?= number_format(rand(10000, 20000)) ?></div>
                        <div class="kpi-trend">
                            <i class="fas fa-arrow-up trend-up"></i>
                            <span><?= number_format($incremento_ingresos, 1) ?>% vs. mes anterior</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Acciones Rápidas -->
        <div class="row">
            <div class="col-12">
                <h5 class="section-title">Acciones Rápidas</h5>
            </div>
        </div>
        <div class="row g-3 quick-actions">
            <div class="col-lg-3 col-md-4 col-6">
                <div class="action-btn" onclick="window.location='./admin/cursos.php?action=new'">
                    <i class="fas fa-plus-circle"></i>
                    <span>Nuevo Curso</span>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 col-6">
                <div class="action-btn" onclick="window.location='./admin/Usuarios.php?action=new'">
                    <i class="fas fa-user-plus"></i>
                    <span>Nuevo Usuario</span>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 col-6">
                <div class="action-btn" onclick="window.location='./admin/inscripciones.php?action=new'">
                    <i class="fas fa-user-graduate"></i>
                    <span>Nueva Inscripción</span>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 col-6">
                <div class="action-btn" onclick="window.location='./admin/pagos.php?action=new'">
                    <i class="fas fa-receipt"></i>
                    <span>Registrar Pago</span>
                </div>
            </div>
        </div>
        
        <!-- Panel principal con gráficos y tablas -->
        <div class="row">
            <!-- Gráfico de tendencias de inscripciones -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Tendencia de Inscripciones</span>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Semana</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary active">Mes</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Año</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="inscripcionesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Estado de pagos -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <span>Estado de Pagos</span>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <div id="pagosChart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Calendario de clases -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Calendario de Clases</span>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus me-1"></i> Añadir Clase
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="classCalendar"></div>
                    </div>
                </div>
            </div>
            
            <!-- Tareas pendientes y solicitudes recientes -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Tareas Pendientes</span>
                        <div>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus me-1"></i> Nueva
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="task-list">
                            <div class="task-item">
                                <div class="task-checkbox">
                                    <input class="form-check-input" type="checkbox" id="task1">
                                </div>
                                <div class="task-content">
                                    <label for="task1">Confirmar inscripciones del curso Teen English</label>
                                </div>
                                <span class="task-priority priority-high">Alta</span>
                            </div>
                            <div class="task-item">
                                <div class="task-checkbox">
                                    <input class="form-check-input" type="checkbox" id="task2">
                                </div>
                                <div class="task-content">
                                    <label for="task2">Actualizar material del curso Business</label>
                                </div>
                                <span class="task-priority priority-medium">Media</span>
                            </div>
                            <div class="task-item">
                                <div class="task-checkbox">
                                    <input class="form-check-input" type="checkbox" id="task3">
                                </div>
                                <div class="task-content">
                                    <label for="task3">Contactar a alumnos con pagos pendientes</label>
                                </div>
                                <span class="task-priority priority-high">Alta</span>
                            </div>
                            <div class="task-item">
                                <div class="task-checkbox">
                                    <input class="form-check-input" type="checkbox" id="task4">
                                </div>
                                <div class="task-content">
                                    <label for="task4">Preparar informe mensual</label>
                                </div>
                                <span class="task-priority priority-low">Baja</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Solicitudes recientes -->
                <div class="card mt-4">
                    <div class="card-header">
                        <span>Solicitudes Recientes</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">María González</h6>
                                    <small class="text-muted">10 min</small>
                                </div>
                                <p class="mb-1">Interesada en Teen English</p>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Jorge Rodríguez</h6>
                                    <small class="text-muted">3 horas</small>
                                </div>
                                <p class="mb-1">Interesado en Business English</p>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Ana Martínez</h6>
                                    <small class="text-muted">1 día</small>
                                </div>
                                <p class="mb-1">Consulta sobre cambio de horario</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabla de últimas inscripciones -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Últimas Inscripciones</span>
                        <a href="./admin/inscripciones.php" class="btn btn-sm btn-outline-primary">Ver Todas</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">Alumno</th>
                                        <th scope="col">Curso</th>
                                        <th scope="col">Fecha</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col">Pago</th>
                                        <th scope="col">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="../images/avatars/1.jpg" alt="Avatar" class="rounded-circle me-2" style="width: 32px; height: 32px;">
                                                <div>Ana Martínez</div>
                                            </div>
                                        </td>
                                        <td>Professional English</td>
                                        <td>12/05/2025</td>
                                        <td><span class="badge bg-success">Activo</span></td>
                                        <td><span class="badge bg-success">Completado</span></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-secondary">Ver</button>
                                                <button type="button" class="btn btn-sm btn-outline-primary">Editar</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="../images/avatars/2.jpg" alt="Avatar" class="rounded-circle me-2" style="width: 32px; height: 32px;">
                                                <div>Miguel Sánchez</div>
                                            </div>
                                        </td>
                                        <td>Business English</td>
                                        <td>10/05/2025</td>
                                        <td><span class="badge bg-success">Activo</span></td>
                                        <td><span class="badge bg-warning text-dark">Pendiente</span></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-secondary">Ver</button>
                                                <button type="button" class="btn btn-sm btn-outline-primary">Editar</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="../images/avatars/3.jpg" alt="Avatar" class="rounded-circle me-2" style="width: 32px; height: 32px;">
                                                <div>Laura Pérez</div>
                                            </div>
                                        </td>
                                        <td>Teen English</td>
                                        <td>08/05/2025</td>
                                        <td><span class="badge bg-secondary">Pendiente</span></td>
                                        <td><span class="badge bg-danger">No Pagado</span></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-secondary">Ver</button>
                                                <button type="button" class="btn btn-sm btn-outline-primary">Editar</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            // Función para comprobar el tamaño de la pantalla
            function checkScreenSize() {
                if (window.innerWidth < 768) {
                    document.body.classList.add('sidebar-hidden');
                } else {
                    document.body.classList.remove('sidebar-hidden');
                }
            }
            
            // Ejecutar al cargar
            checkScreenSize();
            
            // Ejecutar al cambiar el tamaño de la ventana
            window.addEventListener('resize', checkScreenSize);
            
            // Toggle sidebar
            sidebarToggle.addEventListener('click', function() {
                if (window.innerWidth < 768) {
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('active');
                } else {
                    document.body.classList.toggle('sidebar-hidden');
                }
            });
            
            // Ocultar sidebar al hacer clic en overlay
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('active');
            });
            
            // Notifications toggle
            const notificationsBtn = document.getElementById('notificationsBtn');
            const alertBox = document.getElementById('alertBox');
            const closeAlertBox = document.getElementById('closeAlertBox');
            
            notificationsBtn.addEventListener('click', function() {
                alertBox.classList.toggle('show');
            });
            
            closeAlertBox.addEventListener('click', function() {
                alertBox.classList.remove('show');
            });
            
            document.addEventListener('click', function(event) {
                if (!alertBox.contains(event.target) && event.target !== notificationsBtn) {
                    alertBox.classList.remove('show');
                }
            });
            
            // Gráfico de inscripciones
            const inscripcionesCtx = document.getElementById('inscripcionesChart').getContext('2d');
            const inscripcionesChart = new Chart(inscripcionesCtx, {
                type: 'line',
                data: {
                    labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio'],
                    datasets: [{
                        label: 'Inscripciones 2025',
                        data: [65, 59, 80, 81, 90, 95, 87],
                        fill: {
                            target: 'origin',
                            above: 'rgba(74, 107, 219, 0.1)',
                        },
                        borderColor: '#4a6bdb',
                        tension: 0.3,
                        pointBackgroundColor: '#4a6bdb',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                    },
                    {
                        label: 'Inscripciones 2024',
                        data: [45, 52, 60, 65, 75, 80, 73],
                        fill: false,
                        borderColor: 'rgba(74, 107, 219, 0.5)',
                        borderDash: [5, 5],
                        tension: 0.3,
                        pointBackgroundColor: 'rgba(74, 107, 219, 0.5)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 15,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(74, 107, 219, 0.9)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#fff',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: false,
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                stepSize: 20
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    elements: {
                        line: {
                            borderWidth: 3
                        }
                    }
                }
            });
            
            // Gráfico de pagos con ApexCharts
            const pagosSeries = [{
                name: 'Pagos',
                data: [
                    <?= isset($pagos_data['completado']) ? $pagos_data['completado'] : 0 ?>,
                    <?= isset($pagos_data['pendiente']) ? $pagos_data['pendiente'] : 0 ?>,
                    <?= isset($pagos_data['vencido']) ? $pagos_data['vencido'] : 0 ?>,
                    <?= isset($pagos_data['cancelado']) ? $pagos_data['cancelado'] : 0 ?>
                ]
            }];
            
            const pagosOptions = {
                chart: {
                    type: 'donut',
                    height: 350
                },
                labels: ['Completados', 'Pendientes', 'Vencidos', 'Cancelados'],
                colors: ['#28a745', '#ffc107', '#dc3545', '#6c757d'],
                series: [
                    <?= isset($pagos_data['completado']) ? $pagos_data['completado'] : rand(30, 50) ?>,
                    <?= isset($pagos_data['pendiente']) ? $pagos_data['pendiente'] : rand(10, 20) ?>,
                    <?= isset($pagos_data['vencido']) ? $pagos_data['vencido'] : rand(5, 15) ?>,
                    <?= isset($pagos_data['cancelado']) ? $pagos_data['cancelado'] : rand(1, 5) ?>
                ],
                legend: {
                    position: 'bottom'
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '55%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 250
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };
            
            const pagosChart = new ApexCharts(document.querySelector("#pagosChart"), pagosOptions);
            pagosChart.render();
            
            // Inicializar calendario de clases
            const calendarEl = document.getElementById('classCalendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                },
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana',
                    list: 'Lista'
                },
                locale: 'es',
                height: 'auto',
                events: [
                    {
                        title: 'Teen English - Grupo A',
                        start: '2025-05-14T09:00:00',
                        end: '2025-05-14T10:30:00',
                        backgroundColor: '#4a6bdb',
                        borderColor: '#4a6bdb'
                    },
                    {
                        title: 'Business English',
                        start: '2025-05-14T16:00:00',
                        end: '2025-05-14T17:30:00',
                        backgroundColor: '#28a745',
                        borderColor: '#28a745'
                    },
                    {
                        title: 'Professional English',
                        start: '2025-05-15T18:00:00',
                        end: '2025-05-15T19:30:00',
                        backgroundColor: '#e74c3c',
                        borderColor: '#e74c3c'
                    },
                    {
                        title: 'Teen English - Grupo B',
                        start: '2025-05-16T15:00:00',
                        end: '2025-05-16T16:30:00',
                        backgroundColor: '#ffc107',
                        borderColor: '#ffc107'
                    },
                    {
                        title: 'Reunión de profesores',
                        start: '2025-05-17T13:00:00',
                        end: '2025-05-17T14:00:00',
                        backgroundColor: '#17a2b8',
                        borderColor: '#17a2b8'
                    }
                ],
                eventClick: function(info) {
                    alert('Evento: ' + info.event.title);
                    info.el.style.borderColor = 'red';
                }
            });
            calendar.render();
        });
    </script>
</body>
</html>