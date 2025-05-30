<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');
require_once '../config/db.php';

$page_title = "Administracion";
$breadcrumb = [
    ['title' => 'Administracion']
];

$total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$total_cursos = $pdo->query("SELECT COUNT(*) FROM cursos")->fetchColumn();
$total_inscripciones = $pdo->query("SELECT COUNT(*) FROM inscripciones")->fetchColumn();
$nuevas_solicitudes = $pdo->query("SELECT COUNT(*) FROM solicitudes_contacto WHERE estado = 'nuevo'")->fetchColumn();

$stmt = $pdo->query("SELECT estado, COUNT(*) AS cantidad FROM pagos GROUP BY estado");
$pagos_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    
    <?php include 'preloader.php'; ?>
    <?php include 'header.php'; ?>
<!-- Sidebar Toggle Button -->
<div class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </div>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Usuarios
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_usuarios ?></div>
                            <div class="mt-2">
                                <small class="text-success">
                                    <i class="fas fa-arrow-up me-1"></i>5 nuevos esta semana
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Cursos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_cursos ?></div>
                            <div class="mt-2">
                                <small class="text-success">
                                    <i class="fas fa-check-circle me-1"></i>3 cursos activos
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Inscripciones
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_inscripciones ?></div>
                            <div class="mt-2">
                                <small class="text-info">
                                    <i class="fas fa-calendar-plus me-1"></i>12 nuevas este mes
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Solicitudes Nuevas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $nuevas_solicitudes ?></div>
                            <div class="mt-2">
                                <small class="text-warning">
                                    <i class="fas fa-clock me-1"></i>Pendientes de atención
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-envelope fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-bolt me-2"></i>Acciones Rápidas
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <div class="action-card h-100" onclick="location.href='./admin/Usuarios.php'">
                        <div class="action-icon bg-primary-light">
                            <i class="fas fa-user-plus text-primary"></i>
                        </div>
                        <h6 class="mt-3 mb-1">Nuevo Usuario</h6>
                        <p class="text-muted small mb-0">Registrar nuevo estudiante</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="action-card h-100" onclick="location.href='./admin/cursos.php'">
                        <div class="action-icon bg-success-light">
                            <i class="fas fa-book-medical text-success"></i>
                        </div>
                        <h6 class="mt-3 mb-1">Nuevo Curso</h6>
                        <p class="text-muted small mb-0">Crear programa de estudio</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="action-card h-100" onclick="location.href='./admin/inscripciones.php'">
                        <div class="action-icon bg-info-light">
                            <i class="fas fa-user-graduate text-info"></i>
                        </div>
                        <h6 class="mt-3 mb-1">Nueva Inscripción</h6>
                        <p class="text-muted small mb-0">Inscribir estudiante</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="action-card h-100" onclick="location.href='./admin/reportes.php'">
                        <div class="action-icon bg-warning-light">
                            <i class="fas fa-chart-line text-warning"></i>
                        </div>
                        <h6 class="mt-3 mb-1">Reportes</h6>
                        <p class="text-muted small mb-0">Ver estadísticas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-area me-2"></i>Resumen General
                    </h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="chartFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-calendar me-1"></i>Este mes
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="chartFilterDropdown">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-calendar-week me-2"></i>Esta semana</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-calendar-alt me-2"></i>Este mes</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-calendar me-2"></i>Último trimestre</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-calendar-times me-2"></i>Último año</a></li>
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

        <div class="col-lg-4">
            <div class="card shadow mb-4 h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>Estado de Pagos
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="pagosChart"></canvas>
                    </div>
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="status-dot bg-success me-2"></div>
                                <span class="text-sm">Completados</span>
                            </div>
                            <span class="badge bg-success rounded-pill"><?= $pagos_data['completado'] ?? 0 ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="status-dot bg-warning me-2"></div>
                                <span class="text-sm">Pendientes</span>
                            </div>
                            <span class="badge bg-warning rounded-pill"><?= $pagos_data['pendiente'] ?? 0 ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="status-dot bg-danger me-2"></div>
                                <span class="text-sm">Fallidos</span>
                            </div>
                            <span class="badge bg-danger rounded-pill"><?= $pagos_data['fallido'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-envelope-open-text me-2"></i>Solicitudes Recientes
                    </h6>
                    <a href="./admin/solicitudes.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye me-1"></i>Ver todas
                    </a>
                </div>
                <div class="card-body">
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
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-light rounded-circle me-2">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                            María González
                                        </div>
                                    </td>
                                    <td>Teen English</td>
                                    <td><span class="badge bg-danger">Nuevo</span></td>
                                    <td class="text-muted">05/05/2025</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-light rounded-circle me-2">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                            Juan Pérez
                                        </div>
                                    </td>
                                    <td>Kids English</td>
                                    <td><span class="badge bg-warning">En proceso</span></td>
                                    <td class="text-muted">03/05/2025</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-light rounded-circle me-2">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                            Ana Martínez
                                        </div>
                                    </td>
                                    <td>Professional English</td>
                                    <td><span class="badge bg-success">Completado</span></td>
                                    <td class="text-muted">01/05/2025</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-graduation-cap me-2"></i>Inscripciones Recientes
                    </h6>
                    <a href="./admin/inscripciones.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye me-1"></i>Ver todas
                    </a>
                </div>
                <div class="card-body">
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
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-success-light rounded-circle me-2">
                                                <i class="fas fa-user-graduate text-success"></i>
                                            </div>
                                            Luis Ramírez
                                        </div>
                                    </td>
                                    <td>Professional English</td>
                                    <td><span class="badge bg-success">Activo</span></td>
                                    <td><span class="badge bg-success">Pagado</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-success-light rounded-circle me-2">
                                                <i class="fas fa-user-graduate text-success"></i>
                                            </div>
                                            Carmen López
                                        </div>
                                    </td>
                                    <td>Kids English</td>
                                    <td><span class="badge bg-success">Activo</span></td>
                                    <td><span class="badge bg-warning">Pendiente</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-success-light rounded-circle me-2">
                                                <i class="fas fa-user-graduate text-success"></i>
                                            </div>
                                            Pedro Sánchez
                                        </div>
                                    </td>
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
    </div>

    <div class="row mb-4">
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-check me-2"></i>Próximas Clases
                    </h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="calendarFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-filter me-1"></i>Esta semana
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="calendarFilterDropdown">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-calendar-week me-2"></i>Esta semana</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-calendar-alt me-2"></i>Próxima semana</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-calendar me-2"></i>Este mes</a></li>
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
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="course-indicator bg-primary me-2"></div>
                                            Professional English
                                        </div>
                                    </td>
                                    <td>Laura Jiménez</td>
                                    <td>06/05/2025</td>
                                    <td class="text-info">10:00 - 12:00</td>
                                    <td><span class="badge bg-secondary">Aula 3</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="course-indicator bg-success me-2"></div>
                                            Kids English
                                        </div>
                                    </td>
                                    <td>Roberto Fernández</td>
                                    <td>06/05/2025</td>
                                    <td class="text-info">16:00 - 17:30</td>
                                    <td><span class="badge bg-secondary">Aula 1</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="course-indicator bg-warning me-2"></div>
                                            Teen English
                                        </div>
                                    </td>
                                    <td>María Torres</td>
                                    <td>07/05/2025</td>
                                    <td class="text-info">15:00 - 17:00</td>
                                    <td><span class="badge bg-secondary">Aula 2</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-money-bill-wave me-2"></i>Últimos Pagos
                    </h6>
                    <a href="pagos.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye me-1"></i>Ver todos
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th class="border-0 text-muted">
                                        <i class="fas fa-user me-1"></i>Estudiante
                                    </th>
                                    <th class="border-0 text-muted">
                                        <i class="fas fa-dollar-sign me-1"></i>Monto
                                    </th>
                                    <th class="border-0 text-muted">
                                        <i class="fas fa-calendar me-1"></i>Fecha
                                    </th>
                                    <th class="border-0 text-muted">
                                        <i class="fas fa-check-circle me-1"></i>Estado
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="payment-row">
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <div class="feature-icon bg-primary-light rounded-circle me-2">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                            <span class="font-weight-bold">Luis Ramírez</span>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="h6 mb-0 text-success font-weight-bold">$350.00</span>
                                    </td>
                                    <td class="align-middle text-muted">05/05/2025</td>
                                    <td class="align-middle">
                                        <span class="badge rounded-pill bg-success">
                                            <i class="fas fa-check me-1"></i>Completado
                                        </span>
                                    </td>
                                </tr>
                                <tr class="payment-row">
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <div class="feature-icon bg-success-light rounded-circle me-2">
                                                <i class="fas fa-user text-success"></i>
                                            </div>
                                            <span class="font-weight-bold">Ana Martínez</span>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="h6 mb-0 text-success font-weight-bold">$275.00</span>
                                    </td>
                                    <td class="align-middle text-muted">04/05/2025</td>
                                    <td class="align-middle">
                                        <span class="badge rounded-pill bg-success">
                                            <i class="fas fa-check me-1"></i>Completado
                                        </span>
                                    </td>
                                </tr>
                                <tr class="payment-row">
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <div class="feature-icon bg-warning-light rounded-circle me-2">
                                                <i class="fas fa-user text-warning"></i>
                                            </div>
                                            <span class="font-weight-bold">Carlos Pérez</span>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="h6 mb-0 text-warning font-weight-bold">$350.00</span>
                                    </td>
                                    <td class="align-middle text-muted">03/05/2025</td>
                                    <td class="align-middle">
                                        <span class="badge rounded-pill bg-warning">
                                            <i class="fas fa-clock me-1"></i>Pendiente
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bell me-2"></i>Alertas del Sistema
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="alert-item border-left-warning">
                        <div class="alert-content">
                            <div class="feature-icon bg-warning-light rounded-circle">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                            </div>
                            <div class="alert-text">
                                <h6 class="mb-1 font-weight-bold">Pagos Pendientes</h6>
                                <p class="mb-0 text-muted">5 pagos próximos a vencer</p>
                            </div>
                        </div>
                        <a href="#" class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-eye me-1"></i>Ver
                        </a>
                    </div>

                    <div class="alert-item border-left-success">
                        <div class="alert-content">
                            <div class="feature-icon bg-success-light rounded-circle">
                                <i class="fas fa-user-check text-success"></i>
                            </div>
                            <div class="alert-text">
                                <h6 class="mb-1 font-weight-bold">Nuevos Usuarios</h6>
                                <p class="mb-0 text-muted">3 usuarios requieren activación</p>
                            </div>
                        </div>
                        <a href="#" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-check me-1"></i>Revisar
                        </a>
                    </div>

                    <div class="alert-item border-left-info">
                        <div class="alert-content">
                            <div class="feature-icon bg-info-light rounded-circle">
                                <i class="fas fa-calendar-check text-info"></i>
                            </div>
                            <div class="alert-text">
                                <h6 class="mb-1 font-weight-bold">Próximo Curso</h6>
                                <p class="mb-0 text-muted">Teen English inicia el 10/05</p>
                            </div>
                        </div>
                        <a href="#" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-info me-1"></i>Detalles
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tasks me-2"></i>Tareas Pendientes
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="task-item">
                        <div class="task-content">
                            <div class="form-check">
                                <input class="form-check-input task-checkbox" type="checkbox" value="" id="task1">
                                <label class="form-check-label font-weight-bold" for="task1">
                                    Confirmar inscripciones del nuevo curso
                                </label>
                            </div>
                            <div class="task-meta">
                                <span class="badge rounded-pill bg-danger">
                                    <i class="fas fa-exclamation me-1"></i>Alta
                                </span>
                                <small class="text-muted ms-2">
                                    <i class="fas fa-clock me-1"></i>Vence hoy
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="task-item">
                        <div class="task-content">
                            <div class="form-check">
                                <input class="form-check-input task-checkbox" type="checkbox" value="" id="task2">
                                <label class="form-check-label font-weight-bold" for="task2">
                                    Enviar recordatorios de pago
                                </label>
                            </div>
                            <div class="task-meta">
                                <span class="badge rounded-pill bg-info">
                                    <i class="fas fa-info me-1"></i>Media
                                </span>
                                <small class="text-muted ms-2">
                                    <i class="fas fa-calendar me-1"></i>Esta semana
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="task-item">
                        <div class="task-content">
                            <div class="form-check">
                                <input class="form-check-input task-checkbox" type="checkbox" value="" id="task3">
                                <label class="form-check-label font-weight-bold" for="task3">
                                    Actualizar material del curso Professional English
                                </label>
                            </div>
                            <div class="task-meta">
                                <span class="badge rounded-pill bg-secondary">
                                    <i class="fas fa-minus me-1"></i>Baja
                                </span>
                                <small class="text-muted ms-2">
                                    <i class="fas fa-calendar-alt me-1"></i>Próximo mes
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .breadcrumb-item + .breadcrumb-item::before {
            color: rgb(255, 255, 255);
        }
        .breadcrumb-item a {
            color: rgb(255, 255, 255);
        }
        .breadcrumb-item.active {
            color: #ffffff !important;
        }
        .navbar-nav .nav-link {
            color: #ffffff !important;
        }
        .shadow {
            box-shadow: 0 .15rem 1.75rem 0 rgba(58, 59, 69, .15) !important;
        }
        .card {
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 0.5rem;
            overflow: hidden;
            border: none;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }
        .feature-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bg-primary-light {
            background-color: rgba(78, 115, 223, 0.1);
        }
        .bg-success-light {
            background-color: rgba(28, 200, 138, 0.1);
        }
        .bg-info-light {
            background-color: rgba(54, 185, 204, 0.1);
        }
        .bg-warning-light {
            background-color: rgba(246, 194, 62, 0.1);
        }
        .payment-row {
            transition: background-color 0.2s;
        }
        .payment-row:hover {
            background-color: #f8f9fc;
        }
        .table th {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem 0.75rem;
        }
        .alert-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem;
            border-bottom: 1px solid #e3e6f0;
            transition: background-color 0.2s;
        }
        .alert-item:hover {
            background-color: #f8f9fc;
        }
        .alert-item:last-child {
            border-bottom: none;
        }
        .alert-content {
            display: flex;
            align-items: center;
            flex: 1;
        }
        .alert-text {
            margin-left: 1rem;
            flex: 1;
        }
        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }
        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }
        .border-left-info {
            border-left: 0.25rem solid #36b9cc !important;
        }
        .task-item {
            padding: 1.25rem;
            border-bottom: 1px solid #e3e6f0;
            transition: background-color 0.2s;
        }
        .task-item:hover {
            background-color: #f8f9fc;
        }
        .task-item:last-child {
            border-bottom: none;
        }
        .task-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }
        .form-check {
            flex: 1;
        }
        .task-meta {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }
        .task-checkbox {
            transform: scale(1.2);
            margin-right: 0.75rem;
        }
        .task-checkbox:checked + label {
            text-decoration: line-through;
            opacity: 0.6;
        }
        .text-gray-300 {
            color: #dddfeb !important;
        }
        .text-gray-800 {
            color: #5a5c69 !important;
        }
        .font-weight-bold {
            font-weight: 700 !important;
        }
        .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
            font-weight: 600;
        }
        .badge i {
            font-size: 0.7rem;
        }
        .btn {
            border-radius: 0.35rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8rem;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card {
            animation: fadeIn 0.5s ease-out;
        }
        @media (max-width: 768px) {
            .alert-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            .task-content {
                flex-direction: column;
                gap: 0.75rem;
            }
            .task-meta {
                align-self: flex-start;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', toggleSidebar);
            }

            sidebarOverlay.addEventListener('click', function() {
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

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    if (window.innerWidth >= 768) {
                        if (sidebar.classList.contains('show')) {
                            body.classList.remove('sidebar-hidden-by-user');
                        } else {
                            body.classList.add('sidebar-hidden-by-user');
                        }
                    }
                });
            }

            window.addEventListener('load', adjustForScreenSize);
            window.addEventListener('resize', adjustForScreenSize);

            const notificationsBtn = document.getElementById('notificationsBtn');
            const alertBox = document.getElementById('alertBox');
            const closeAlertBox = document.getElementById('closeAlertBox');

            if (notificationsBtn && alertBox) {
                notificationsBtn.addEventListener('click', function() {
                    alertBox.classList.toggle('show');
                });

                if (closeAlertBox) {
                    closeAlertBox.addEventListener('click', function() {
                        alertBox.classList.remove('show');
                    });
                }

                document.addEventListener('click', function(event) {
                    if (!alertBox.contains(event.target) && event.target !== notificationsBtn) {
                        alertBox.classList.remove('show');
                    }
                });
            }

            const taskCheckboxes = document.querySelectorAll('.task-checkbox');
            taskCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const taskItem = this.closest('.task-item');
                    if (this.checked) {
                        taskItem.style.opacity = '0.6';
                        setTimeout(() => {
                            taskItem.style.transform = 'translateX(10px)';
                        }, 100);
                    } else {
                        taskItem.style.opacity = '1';
                        taskItem.style.transform = 'translateX(0)';
                    }
                });
            });

            const paymentRows = document.querySelectorAll('.payment-row');
            paymentRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(5px)';
                });

                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                });
            });

            const cards = document.querySelectorAll('.card');
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const cardObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationDelay = Math.random() * 0.3 + 's';
                        entry.target.classList.add('animate-in');
                    }
                });
            }, observerOptions);

            cards.forEach(card => {
                cardObserver.observe(card);
            });

            if (typeof Chart !== 'undefined') {
                const resumenChart = document.getElementById('resumenChart');
                if (resumenChart) {
                    const resumenCtx = resumenChart.getContext('2d');
                    new Chart(resumenCtx, {
                        type: 'line',
                        data: {
                            labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo'],
                            datasets: [{
                                label: 'Inscripciones',
                                data: [12, 19, 10, 15, 22],
                                backgroundColor: 'rgba(78, 115, 223, 0.2)',
                                borderColor: 'rgba(78, 115, 223, 1)',
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
                }

                const pagosChart = document.getElementById('pagosChart');
                if (pagosChart) {
                    const pagosCtx = pagosChart.getContext('2d');
                    new Chart(pagosCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Completados', 'Pendientes', 'Fallidos'],
                            datasets: [{
                                data: [75, 20, 5],
                                backgroundColor: [
                                    'rgba(28, 200, 138, 0.8)',
                                    'rgba(246, 194, 62, 0.8)',
                                    'rgba(231, 76, 60, 0.8)'
                                ],
                                borderColor: [
                                    'rgba(28, 200, 138, 1)',
                                    'rgba(246, 194, 62, 1)',
                                    'rgba(231, 76, 60, 1)'
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
                }
            }
        });
    </script>
</body>
</html>
