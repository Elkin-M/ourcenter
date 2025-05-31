<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');

// cursos.php
require_once '../../config/db.php';

// Obtener lista de cursos con información adicional
$stmt = $pdo->query("
    SELECT c.id, c.nombre, c.descripcion, 
           COUNT(DISTINCT i.id) as num_inscritos,
           c.fecha_inicio, c.duracion_horas, c.precio, c.imagen_url, c.estado
    FROM cursos c
    LEFT JOIN inscripciones i ON c.id = i.curso_id
    GROUP BY c.id
    ORDER BY c.id DESC
");
$cursos = $stmt->fetchAll();

// Verificar si hay mensaje de confirmación en la sesión
$mensaje = '';
$tipoMensaje = '';
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $tipoMensaje = $_SESSION['tipo_mensaje'];
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
}

// Obtener filtros
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Aplicar filtros si están definidos
$cursos_filtrados = $cursos;
if ($status_filter !== 'all') {
    $cursos_filtrados = array_filter($cursos, function($curso) use ($status_filter) {
        return strtolower($curso['estado']) === strtolower($status_filter);
    });
}

if (!empty($search)) {
    $cursos_filtrados = array_filter($cursos_filtrados, function($curso) use ($search) {
        return stripos($curso['nombre'], $search) !== false || 
               stripos($curso['descripcion'], $search) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cursos - Our Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <link rel="stylesheet" href="../../css/dashboard.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    
    <style>
        :root {
            --primary-color: #4e73df;
            --primary-color-light: rgba(78, 115, 223, 0.1);
            --success-color: #1cc88a;
            --success-color-light: rgba(28, 200, 138, 0.1);
            --info-color: #36b9cc;
            --info-color-light: rgba(54, 185, 204, 0.1);
            --warning-color: #f6c23e;
            --warning-color-light: rgba(246, 194, 62, 0.1);
            --danger-color: #e74a3b;
            --light-bg: #f8f9fc;
            --dark-bg: #5a5c69;
            --border-color: #e3e6f0;
            --text-muted: #858796;
            --shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15);
        }

        body {
            background-color: var(--light-bg);
            font-family: "Nunito",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";
        }

        .page-content {
            padding: 30px;
            background-color: var(--light-bg);
            min-height: calc(100vh - 60px);
        }

        .breadcrumb-item+.breadcrumb-item::before {
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

        /* Cards de estadísticas */
        .border-left-primary {
            border-left: 0.25rem solid var(--primary-color) !important;
        }

        .border-left-success {
            border-left: 0.25rem solid var(--success-color) !important;
        }

        .border-left-info {
            border-left: 0.25rem solid var(--info-color) !important;
        }

        .border-left-warning {
            border-left: 0.25rem solid var(--warning-color) !important;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .text-success {
            color: var(--success-color) !important;
        }

        .text-info {
            color: var(--info-color) !important;
        }

        .text-warning {
            color: var(--warning-color) !important;
        }

        .text-gray-300 {
            color: #dddfeb !important;
        }

        .text-gray-800 {
            color: var(--dark-bg) !important;
        }

        .shadow {
            box-shadow: var(--shadow) !important;
        }

        .font-weight-bold {
            font-weight: 700 !important;
        }

        .text-xs {
            font-size: .7rem;
        }

        /* Cards de cursos */
        .curso-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 0.5rem;
            overflow: hidden;
            border: 1px solid var(--border-color);
            background-color: white;
            box-shadow: var(--shadow);
        }

        .curso-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .curso-imagen {
    object-fit: cover;
    object-position: center;
    width: 100%;
    height: auto;
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
    transition: transform 0.3s;
    background-color: #f8f9fc;
    background-image: linear-gradient(45deg, #e9ecef 25%, transparent 25%), 
                      linear-gradient(-45deg, #e9ecef 25%, transparent 25%), 
                      linear-gradient(45deg, transparent 75%, #e9ecef 75%), 
                      linear-gradient(-45deg, transparent 75%, #e9ecef 75%);
    background-size: 20px 20px;
    background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
}
.imagen-container {
    position: relative;
    overflow: hidden;
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
    background-color: #f8f9fc;
    min-height: auto;
    display: flex;
    align-items: center;
    justify-content: center;
}

.imagen-placeholder {
    color: #adb5bd;
    font-size: 3rem;
    opacity: 0.5;
}

.imagen-error {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    text-align: center;
    padding: 2rem;
}
        .curso-imagen:hover {
            transform: scale(1.05);
        }

        .curso-meta {
            background-color: var(--light-bg);
            border-radius: 0.5rem;
            padding: 1rem 0.5rem;
            margin: -0.5rem -0.5rem 1rem -0.5rem;
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            border-radius: 50%;
        }

        .bg-primary-light {
            background-color: var(--primary-color-light);
        }

        .bg-success-light {
            background-color: var(--success-color-light);
        }

        .bg-info-light {
            background-color: var(--info-color-light);
        }

        .bg-warning-light {
            background-color: var(--warning-color-light);
        }

        .detail-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }

        .detail-label {
            font-weight: 600;
            margin-right: 0.25rem;
        }

        .detail-value {
            color: var(--text-muted);
        }

        .card-header {
            background-color: var(--light-bg);
            border-bottom: 1px solid var(--border-color);
        }

        .precio-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background: linear-gradient(135deg, rgba(78, 115, 223, 0.95) 0%, rgba(54, 115, 255, 0.95) 100%);
    color: white;
    padding: 8px 12px;
    border-radius: 25px;
    font-weight: bold;
    font-size: 0.8rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    backdrop-filter: blur(5px);
    z-index: 2;
}

.estado-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 2;
}

        .btn-floating {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            z-index: 100;
        }

        .progress {
            height: 0.5rem;
            border-radius: 10rem;
            background-color: #eaecf4;
        }

        .progress-bar {
            border-radius: 10rem;
        }

        /* Estados de cursos */
        .estado-activo {
            background-color: var(--success-color);
            color: white;
        }

        .estado-inactivo {
            background-color: var(--danger-color);
            color: white;
        }

        .estado-pendiente {
            background-color: var(--warning-color);
            color: #212529;
        }

        /* Tabla responsiva */
        .table {
            color: var(--dark-bg);
        }

        .table th {
            border-top: none;
            font-weight: 800;
            font-size: .65rem;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        /* Filtros mejorados */
        .filter-section {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: var(--shadow);
        }

        @media (max-width: 768px) {
    .page-content {
        padding: 10px;
    }
    
    .feature-icon {
        width: 35px;
        height: 35px;
        font-size: 0.9rem;
    }
    
    .curso-meta {
        padding: 0.75rem 0.25rem;
        margin: -0.25rem -0.25rem 0.75rem -0.25rem;
    }
    
    .curso-meta .col-4 h6 {
        font-size: 0.9rem;
    }
    
    .curso-meta .col-4 small {
        font-size: 0.7rem;
    }
    
    .card-title {
        font-size: 1.1rem;
        line-height: 1.3;
    }
    
    .card-text {
        font-size: 0.9rem;
        line-height: 1.4;
    }
    
    .btn-group .btn {
        font-size: 0.8rem;
        padding: 0.4rem 0.6rem;
    }
    
    .detail-item {
        font-size: 0.8rem;
        margin-bottom: 0.3rem;
    }
    
    .precio-badge {
        top: 10px;
        left: 10px;
        padding: 6px 10px;
        font-size: 0.75rem;
    }
    
    .estado-badge {
        top: 10px;
        right: 10px;
    }
    
    .estado-badge .badge {
        font-size: 0.7rem;
        padding: 0.4em 0.6em;
    }
}

/* Añadir también estilos para móviles más pequeños */
@media (max-width: 576px) {
    .col-lg-4.col-md-6 {
        padding-left: 5px;
        padding-right: 5px;
    }
    
    .curso-card {
        margin-bottom: 15px;
    }
    
    .card-body {
        padding: 1rem 0.75rem;
    }
    
    .btn-floating {
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    .filter-section .card-body {
        padding: 1rem;
    }
    
    .filter-section .row.g-3 {
        --bs-gutter-x: 0.5rem;
    }
    .mas{
        margin-top: 10px !important;
    }
    .curso-imagen{
        height: 150px;
    }
    .imagen-container{
        min-height: 0;
    }
}
/* Mejoras de espaciado */
.card-body {
    padding: 1.5rem;
}

.card-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border-color);
}

.mb-4 {
    margin-bottom: 1.75rem !important;
}

/* Mejora para botones de acción */
.btn-group .btn {
    transition: all 0.2s ease;
}

.btn-group .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Mejora para dropdowns */
.dropdown-menu {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    border-radius: 0.5rem;
}

.dropdown-item {
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background-color: var(--primary-color-light);
    color: var(--primary-color);
}
.navbar-nav .dropdown-menu{
        position: absolute !important;
}
    </style>
</head>
<body>
    <!-- Preloader (mantenido del original) -->
    <?php include '../preloader.php';?>
    <?php include '../header.php'; ?>

    <!-- Sidebar Toggle Button -->
    <div class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </div>

    <div class="container-fluid">
        <div class="page-content">
            <!-- Mensajes de confirmación -->
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
                    <?= $mensaje ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-book me-2"></i>Gestión de Cursos</h1>
                <a href="nuevo_curso.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-1"></i>Nuevo Curso
                </a>
            </div>

            <!-- Resumen estadístico -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total de Cursos</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($cursos) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-book fa-2x text-gray-300"></i>
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
                                        Total de Estudiantes</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $totalInscritos = 0;
                                        foreach ($cursos as $curso) {
                                            $totalInscritos += $curso['num_inscritos'];
                                        }
                                        echo $totalInscritos;
                                        ?>
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
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Cursos Activos</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $cursosActivos = 0;
                                        foreach ($cursos as $curso) {
                                            if (strtolower($curso['estado']) == 'activo') {
                                                $cursosActivos++;
                                            }
                                        }
                                        echo $cursosActivos;
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                        Próximos Cursos</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $cursosPendientes = 0;
                                        foreach ($cursos as $curso) {
                                            if (strtolower($curso['estado']) == 'pendiente') {
                                                $cursosPendientes++;
                                            }
                                        }
                                        echo $cursosPendientes;
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card shadow mb-4 filter-section">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter me-2"></i>Filtrar Cursos</h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Buscar</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Nombre o descripción..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Estado</label>
                            <select class="form-select" id="status" name="status">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>
                                    Todos los estados
                                </option>
                                <option value="activo" <?php echo $status_filter === 'activo' ? 'selected' : ''; ?>>
                                    Activos
                                </option>
                                <option value="inactivo" <?php echo $status_filter === 'inactivo' ? 'selected' : ''; ?>>
                                    Inactivos
                                </option>
                                <option value="pendiente" <?php echo $status_filter === 'pendiente' ? 'selected' : ''; ?>>
                                    Pendientes
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="btn-group mt-4" role="group" aria-label="Vista de cursos">
                                <button type="button" class="btn btn-outline-primary active" id="vista-cards">
                                    <i class="fas fa-th-large"></i>
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="vista-tabla">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-1"></i> Filtrar
                            </button>
                            <a href="cursos.php" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-1"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Vista de Tarjetas -->
            <div class="row" id="vista-cards-container">
                <?php if (empty($cursos_filtrados)): ?>
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-book fa-4x text-muted mb-4"></i>
                                <h4 class="text-muted">No hay cursos disponibles</h4>
                                <p class="text-muted mb-4">
                                    <?php if (!empty($search) || $status_filter !== 'all'): ?>
                                        No se encontraron cursos que coincidan con los filtros aplicados.
                                    <?php else: ?>
                                        Comienza añadiendo un nuevo curso.
                                    <?php endif; ?>
                                </p>
                                <a href="nuevo_curso.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus me-2"></i>Crear Curso
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($cursos_filtrados as $curso): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card curso-card shadow h-100">
                            <div class="position-relative imagen-container">
                                <?php if (!empty($curso['imagen_url'])): ?>
                                    <img src="../.<?= htmlspecialchars($curso['imagen_url']) ?>" 
                                        class="curso-imagen" 
                                        alt="<?= htmlspecialchars($curso['nombre']) ?>"
                                        onerror="this.parentElement.innerHTML='<div class=\'imagen-error\'><div><i class=\'fas fa-book fa-2x mb-2\'></i><br><?= htmlspecialchars($curso['nombre']) ?></div></div>'">
                                <?php else: ?>
                                    <div class="imagen-error">
                                        <div>
                                            <i class="fas fa-book fa-2x mb-2"></i><br>
                                            <?= htmlspecialchars($curso['nombre']) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                    <div class="precio-badge">$<?= number_format($curso['precio'] ?? 0, 2) ?></div>
                                    <div class="estado-badge">
                                        <?php if (strtolower($curso['estado']) == 'activo'): ?>
                                            <span class="badge rounded-pill bg-success">Activo</span>
                                        <?php elseif (strtolower($curso['estado']) == 'inactivo'): ?>
                                            <span class="badge rounded-pill bg-danger">Inactivo</span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill bg-warning text-dark">Pendiente</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <div class="curso-meta mb-3">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="feature-icon bg-primary-light mb-2">
                                                    <i class="fas fa-users text-primary"></i>
                                                </div>
                                                <h6 class="mb-0"><?= $curso['num_inscritos'] ?></h6>
                                                <small class="text-muted">Estudiantes</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="feature-icon bg-success-light mb-2">
                                                    <i class="fas fa-clock text-success"></i>
                                                </div>
                                                <h6 class="mb-0"><?= !empty($curso['duracion_horas']) ? $curso['duracion_horas'] : '0' ?></h6>
                                                <small class="text-muted">Semanas</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="feature-icon bg-info-light mb-2">
                                                    <i class="fas fa-calendar-alt text-info"></i>
                                                </div>
                                                <h6 class="mb-0"><?= !empty($curso['fecha_inicio']) ? date('d/m', strtotime($curso['fecha_inicio'])) : 'N/D' ?></h6>
                                                <small class="text-muted">Inicio</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <h5 class="card-title text-primary font-weight-bold"><?= htmlspecialchars($curso['nombre']) ?></h5>
                                    <p class="card-text text-muted"><?= strlen($curso['descripcion']) > 100 ? htmlspecialchars(substr($curso['descripcion'], 0, 100) . '...') : htmlspecialchars($curso['descripcion']) ?></p>
                                    
                                    <div class="curso-details">
                                        <?php if (!empty($curso['fecha_inicio'])): ?>
                                            <div class="detail-item">
                                                <i class="fas fa-calendar-alt text-muted me-2"></i>
                                                <span class="detail-label">Fecha de inicio:</span>
                                                <span class="detail-value"><?= date('d/m/Y', strtotime($curso['fecha_inicio'])) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="detail-item">
                                            <i class="fas fa-dollar-sign text-muted me-2"></i>
                                            <span class="detail-label">Precio:</span>
                                            <span class="detail-value">$<?= number_format($curso['precio'] ?? 0, 2) ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-footer bg-transparent">
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-primary ver-detalles" data-id="<?= $curso['id'] ?>">
                                            <i class="fas fa-eye me-1"></i>Detalles
                                        </button>
                                        <a href="./actions/editar_curso.php?id=<?= $curso['id'] ?>" class="btn btn-warning">
                                            <i class="fas fa-edit me-1"></i>Editar
                                        </a>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-secondary dropdown-toggle" 
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="./actions/inscritos.php?curso_id=<?= $curso['id'] ?>">
                                                        <i class="fas fa-users me-2"></i>Ver Inscritos
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button type="button" class="dropdown-item text-danger eliminar-curso" 
                                                            data-id="<?= $curso['id'] ?>" data-nombre="<?= htmlspecialchars($curso['nombre']) ?>">
                                                        <i class="fas fa-trash-alt me-2"></i>Eliminar
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Vista de Tabla (inicialmente oculta) -->
            <div class="card shadow" id="vista-tabla-container" style="display: none;">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Lista de Cursos</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tabla-cursos">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Estudiantes</th>
                                    <th>Fecha Inicio</th>
                                    <th>Precio</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cursos_filtrados as $curso): ?>
                                    <tr>
                                        <td class="font-weight-bold"><?= $curso['id'] ?></td>
                                        <td class="font-weight-bold"><?= htmlspecialchars($curso['nombre']) ?></td>
                                        <td><?= strlen($curso['descripcion']) > 50 ? htmlspecialchars(substr($curso['descripcion'], 0, 50) . '...') : htmlspecialchars($curso['descripcion']) ?></td>
                                        <td><span class="badge bg-info"><?= $curso['num_inscritos'] ?></span></td>
                                        <td><?= !empty($curso['fecha_inicio']) ? date('d/m/Y', strtotime($curso['fecha_inicio'])) : 'No definida' ?></td>
                                        <td class="font-weight-bold">$<?= number_format($curso['precio'] ?? 0, 2) ?></td>
                                        <td>
                                            <?php if (strtolower($curso['estado']) == 'activo'): ?>
                                                <span class="badge rounded-pill bg-success">Activo</span>
                                            <?php elseif (strtolower($curso['estado']) == 'inactivo'): ?>
                                                <span class="badge rounded-pill bg-danger">Inactivo</span>
                                                <?php else: ?>
                                               <span class="badge rounded-pill bg-warning text-dark">Pendiente</span>
                                           <?php endif; ?>
                                       </td>
                                       <td>
                                           <div class="btn-group" role="group">
                                               <button type="button" class="btn btn-sm btn-primary ver-detalles" data-id="<?= $curso['id'] ?>">
                                                   <i class="fas fa-eye"></i>
                                               </button>
                                               <a href="editar_curso.php?id=<?= $curso['id'] ?>" class="btn btn-sm btn-warning">
                                                   <i class="fas fa-edit"></i>
                                               </a>
                                               <a href="inscritos.php?curso_id=<?= $curso['id'] ?>" class="btn btn-sm btn-info">
                                                   <i class="fas fa-users"></i>
                                               </a>
                                               <button type="button" class="btn btn-sm btn-danger eliminar-curso" 
                                                       data-id="<?= $curso['id'] ?>" data-nombre="<?= htmlspecialchars($curso['nombre']) ?>">
                                                   <i class="fas fa-trash-alt"></i>
                                               </button>
                                           </div>
                                       </td>
                                   </tr>
                               <?php endforeach; ?>
                           </tbody>
                       </table>
                   </div>
               </div>
           </div>

       </div>
   </div>

   <!-- Botón flotante para nuevo curso -->
   <a href="nuevo_curso.php" class="btn btn-primary btn-floating" title="Nuevo Curso">
       <i class="fas fa-plus mas" style="margin-top: 15px;"></i>
   </a>

   <!-- Modal para detalles del curso -->
   <div class="modal fade" id="modalDetallesCurso" tabindex="-1" aria-labelledby="modalDetallesCursoLabel" aria-hidden="true">
       <div class="modal-dialog modal-lg">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="modalDetallesCursoLabel">
                       <i class="fas fa-book me-2"></i>Detalles del Curso
                   </h5>
                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">
                   <div id="contenido-detalles">
                       <div class="d-flex justify-content-center">
                           <div class="spinner-border text-primary" role="status">
                               <span class="visually-hidden">Cargando...</span>
                           </div>
                       </div>
                   </div>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                   <button type="button" class="btn btn-primary" id="btn-editar-modal">
                       <i class="fas fa-edit me-1"></i>Editar Curso
                   </button>
               </div>
           </div>
       </div>
   </div>

   <!-- Scripts -->
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
   <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
   
   <!-- DataTables JS -->
   <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
   <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
   
   <!-- SweetAlert2 -->
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
   
   <script src="../../js/dashboard.js"></script>
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
   <script>
       $(document).ready(function() {
           // Inicializar DataTable
           $('#tabla-cursos').DataTable({
               "language": {
                   "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
               },
               "pageLength": 10,
               "order": [[0, "desc"]],
               "columnDefs": [
                   { "orderable": false, "targets": 7 } // Columna de acciones no ordenable
               ]
           });

           // Alternar entre vista de cards y tabla
           $('#vista-cards').click(function() {
               $(this).addClass('active');
               $('#vista-tabla').removeClass('active');
               $('#vista-cards-container').show();
               $('#vista-tabla-container').hide();
           });

           $('#vista-tabla').click(function() {
               $(this).addClass('active');
               $('#vista-cards').removeClass('active');
               $('#vista-cards-container').hide();
               $('#vista-tabla-container').show();
           });

           // Ver detalles del curso
           $('.ver-detalles').click(function() {
               const cursoId = $(this).data('id');
               $('#modalDetallesCurso').modal('show');
               
               // Cargar detalles del curso
               $.ajax({
                   url: './actions/obtener_detalles_curso.php',
                   method: 'GET',
                   data: { id: cursoId },
                   success: function(response) {
                       $('#contenido-detalles').html(response);
                       $('#btn-editar-modal').data('id', cursoId);
                   },
                   error: function() {
                       $('#contenido-detalles').html(`
                           <div class="alert alert-danger">
                               <i class="fas fa-exclamation-triangle me-2"></i>
                               Error al cargar los detalles del curso.
                           </div>
                       `);
                   }
               });
           });

           // Botón editar en el modal
           $('#btn-editar-modal').click(function() {
               const cursoId = $(this).data('id');
               window.location.href = `actions/editar_curso.php?id=${cursoId}`;
           });

           // Eliminar curso
           $('.eliminar-curso').click(function() {
               const cursoId = $(this).data('id');
               const cursoNombre = $(this).data('nombre');

               Swal.fire({
                   title: '¿Estás seguro?',
                   text: `¿Deseas eliminar el curso "${cursoNombre}"? Esta acción no se puede deshacer.`,
                   icon: 'warning',
                   showCancelButton: true,
                   confirmButtonColor: '#d33',
                   cancelButtonColor: '#3085d6',
                   confirmButtonText: 'Sí, eliminar',
                   cancelButtonText: 'Cancelar'
               }).then((result) => {
                   if (result.isConfirmed) {
                       $.ajax({
                           url: './actions/eliminar_curso.php',
                           method: 'POST',
                           data: { id: cursoId },
                           dataType: 'json',
                           success: function(response) {
                               if (response.success) {
                                   Swal.fire({
                                       title: '¡Eliminado!',
                                       text: response.message,
                                       icon: 'success',
                                       timer: 2000,
                                       showConfirmButton: false
                                   }).then(() => {
                                       location.reload();
                                   });
                               } else {
                                   Swal.fire('Error', response.message, 'error');
                               }
                           },
                           error: function() {
                               Swal.fire('Error', 'Ocurrió un error al eliminar el curso.', 'error');
                           }
                       });
                   }
               });
           });

           // Ocultar alertas automáticamente
           $('.alert').delay(5000).fadeOut('slow');
       });
   </script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>