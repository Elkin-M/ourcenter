<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');

require_once '../../config/db.php';
$current_page = 'Usuarios';

// Paginación
$registros_por_pagina = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina - 1) * $registros_por_pagina;

// Búsqueda
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
$where = '';
if (!empty($busqueda)) {
    $where = " WHERE nombre LIKE :busqueda OR email LIKE :busqueda OR apellido LIKE :busqueda";
}

// Contar total de registros para la paginación
$sql_total = "SELECT COUNT(*) FROM usuarios" . $where;
$stmt_total = $pdo->prepare($sql_total);
if (!empty($busqueda)) {
    $stmt_total->bindValue(':busqueda', "%$busqueda%", PDO::PARAM_STR);
}
$stmt_total->execute();
$total_registros = $stmt_total->fetchColumn();
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Consulta principal con paginación y búsqueda
$sql = "SELECT u.id, u.nombre, u.apellido, u.email, u.estado, u.fecha_creacion, r.nombre as rol_nombre 
        FROM usuarios u 
        JOIN roles r ON u.rol_id = r.id" . $where . " 
        ORDER BY u.id DESC 
        LIMIT :inicio, :registros_por_pagina";

$stmt = $pdo->prepare($sql);
if (!empty($busqueda)) {
    $stmt->bindValue(':busqueda', "%$busqueda%", PDO::PARAM_STR);
}
$stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
$stmt->bindValue(':registros_por_pagina', $registros_por_pagina, PDO::PARAM_INT);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estadísticas básicas
$stmt_stats = $pdo->query("SELECT 
                            COUNT(*) as total_usuarios,
                            SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as usuarios_activos,
                            SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as usuarios_inactivos,
                            SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as usuarios_pendientes
                          FROM usuarios");
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

// Obtener roles para filtrar
$stmt_roles = $pdo->query("SELECT id, nombre FROM roles ORDER BY nombre");
$roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - Our Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
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
    
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card {
            border-left: 4px solid;
            min-height: 100px;
        }
        
        .stat-card .card-title {
            font-size: 0.9rem;
            text-transform: uppercase;
            font-weight: 600;
            color: #6c757d;
        }
        
        .stat-card .card-value {
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .stat-card .card-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        .stat-card.total {
            border-left-color: #4e73df;
        }
        
        .stat-card.activos {
            border-left-color: #1cc88a;
        }
        
        .stat-card.inactivos {
            border-left-color: #e74a3b;
        }
        
        .stat-card.pendientes {
            border-left-color: #f6c23e;
        }
        
        .btn-circle {
            width: 30px;
            height: 30px;
            padding: 0;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            font-size: 0.75rem;
        }
        
        .estado-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .estado-activo {
            background-color: rgba(28, 200, 138, 0.2);
            color: #1cc88a;
        }
        
        .estado-inactivo {
            background-color: rgba(231, 74, 59, 0.2);
            color: #e74a3b;
        }
        
        .estado-pendiente {
            background-color: rgba(246, 194, 62, 0.2);
            color: #f6c23e;
        }
        
        .search-container {
            position: relative;
        }
        
        .search-container .form-control {
            padding-left: 2.5rem;
            border-radius: 50px;
        }
        
        .search-container .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .action-buttons .btn {
            margin-right: 5px;
        }
        
        .page-title {
            font-weight: 700;
            color: white;
            margin-bottom: 1.5rem;
        }
        
        .table thead th {
            background-color: #f8f9fc;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        
        .filter-dropdown {
            width: 150px;
        }
        
        .pagination .page-link {
            color: #4e73df;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: 600;
            color: #4e73df;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            visibility: hidden;
            opacity: 0;
            transition: visibility 0s, opacity 0.3s;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
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
                <div class="logo-text" style="
    margin-left: 10px;">OUR CENTER</div>
                <!-- <div class="logo-short">OC</div> -->
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
                <a class="nav-link active" href="/ourcenter/templates/admin/Usuarios.php">
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
                <a class="nav-link" href="/ourcenter/templates/admin/solicitudes.php">
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

<!-- <div class="main-content" id="mainContent"> -->
    <div class="container-fluid mt-4 px-4">
        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loadingOverlay">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
        
        <!-- Page Heading -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title" style="color: black;">Gestión de Usuarios</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoUsuarioModal">
                <i class="fas fa-user-plus me-2"></i>Nuevo Usuario
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card total h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="card-title text-uppercase mb-1">Total Usuarios</div>
                                <div class="card-value"><?= $stats['total_usuarios'] ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users card-icon text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card activos h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="card-title text-uppercase mb-1">Usuarios Activos</div>
                                <div class="card-value"><?= $stats['usuarios_activos'] ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-check card-icon text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card inactivos h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="card-title text-uppercase mb-1">Usuarios Inactivos</div>
                                <div class="card-value"><?= $stats['usuarios_inactivos'] ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-slash card-icon text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card pendientes h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="card-title text-uppercase mb-1">Pendientes</div>
                                <div class="card-value"><?= $stats['usuarios_pendientes'] ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-clock card-icon text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <h5 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-users me-2"></i>Lista de Usuarios
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-md-end">
                            <div class="search-container me-2">
                                <i class="fas fa-search search-icon"></i>
                                <form action="" method="GET" class="d-inline">
                                    <input type="text" name="busqueda" class="form-control" placeholder="Buscar usuario..." value="<?= htmlspecialchars($busqueda) ?>">
                                </form>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle filter-dropdown" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-filter me-1"></i>Filtrar
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <li><a class="dropdown-item" href="?estado=activo">Activos</a></li>
                                    <li><a class="dropdown-item" href="?estado=inactivo">Inactivos</a></li>
                                    <li><a class="dropdown-item" href="?estado=pendiente">Pendientes</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <?php foreach ($roles as $rol): ?>
                                        <li><a class="dropdown-item" href="?rol_id=<?= $rol['id'] ?>"><?= htmlspecialchars($rol['nombre']) ?></a></li>
                                    <?php endforeach; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="?">Mostrar todos</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="usuariosTable">
                        <thead>
                            <tr>
                                <th width="5%">ID</th>
                                <th width="20%">Usuario</th>
                                <th width="20%">Email</th>
                                <th width="10%">Rol</th>
                                <th width="10%">Estado</th>
                                <th width="15%">Fecha de Registro</th>
                                <th width="20%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($usuarios) > 0): ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?= $usuario['id'] ?></td>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <?= strtoupper(substr($usuario['nombre'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($usuario['email']) ?></td>
                                        <td><?= htmlspecialchars($usuario['rol_nombre']) ?></td>
                                        <td>
                                            <?php 
                                            $estado_class = '';
                                            switch($usuario['estado']) {
                                                case 'activo':
                                                    $estado_class = 'estado-activo';
                                                    break;
                                                case 'inactivo':
                                                    $estado_class = 'estado-inactivo';
                                                    break;
                                                case 'pendiente':
                                                    $estado_class = 'estado-pendiente';
                                                    break;
                                            }
                                            ?>
                                            <span class="estado-badge <?= $estado_class ?>"><?= ucfirst($usuario['estado']) ?></span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($usuario['fecha_creacion'])) ?></td>
                                        <td class="action-buttons">
                                            <button class="btn btn-sm btn-info text-white view-btn" data-id="<?= $usuario['id'] ?>" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="editar_usuario.php?id=<?= $usuario['id'] ?>" class="btn btn-sm btn-warning text-white" title="Editar usuario">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $usuario['id'] ?>" title="Eliminar usuario">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php if ($usuario['estado'] === 'pendiente'): ?>
                                                <button class="btn btn-sm btn-success activate-btn" data-id="<?= $usuario['id'] ?>" title="Activar usuario">
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No se encontraron usuarios</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_paginas > 1): ?>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <p class="text-muted">Mostrando <?= count($usuarios) ?> de <?= $total_registros ?> usuarios</p>
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <li class="page-item <?= ($pagina <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?pagina=<?= $pagina - 1 ?><?= !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : '' ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <?php for ($i = max(1, $pagina - 2); $i <= min($pagina + 2, $total_paginas); $i++): ?>
                                <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?pagina=<?= $i ?><?= !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?= ($pagina >= $total_paginas) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?pagina=<?= $pagina + 1 ?><?= !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : '' ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal Nuevo Usuario -->
    <div class="modal fade" id="nuevoUsuarioModal" tabindex="-1" aria-labelledby="nuevoUsuarioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="nuevoUsuarioModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Nuevo Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="nuevoUsuarioForm" action="guardar_usuario.php" method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label for="apellido" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="rol_id" class="form-label">Rol</label>
                                <select class="form-select" id="rol_id" name="rol_id" required>
                                    <option value="">Seleccione un rol</option>
                                    <?php foreach ($roles as $rol): ?>
                                        <option value="<?= $rol['id'] ?>"><?= htmlspecialchars($rol['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono">
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                                <select class="form-select" id="tipo_documento" name="tipo_documento">
                                    <option value="">Seleccione un tipo</option>
                                    <option value="dni">DNI</option>
                                    <option value="pasaporte">Pasaporte</option>
                                    <option value="nie">NIE</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="documento_identidad" class="form-label">Número de Documento</label>
                                <input type="text" class="form-control" id="documento_identidad" name="documento_identidad">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="ciudad" class="form-label">Ciudad</label>
                                <input type="text" class="form-control" id="ciudad" name="ciudad">
                            </div>
                            <div class="col-md-6">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="pendiente" selected>Pendiente</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="nuevoUsuarioForm" class="btn btn-primary">Guardar Usuario</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Ver Usuario -->
    <div class="modal fade" id="verUsuarioModal" tabindex="-1" aria-labelledby="verUsuarioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="verUsuarioModalLabel">
                        <i class="fas fa-user me-2"></i>Detalles del Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="avatar-preview mx-auto mb-3" style="width: 100px; height: 100px; background-color: #f0f2f5; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: bold; color: #4e73df;">
                            <span id="userInitial"></span>
                        </div>
                        <h5 id="userName" class="mb-0"></h5>
                        <p id="userEmail" class="text-muted"></p>
                        <span id="userStatus" class="estado-badge"></span>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-uppercase text-muted mb-2"><i class="fas fa-id-card me-2"></i>Información Personal</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <p class="mb-1 text-muted small">Documento</p>
                                <p id="userDocument" class="mb-0">-</p>
                            </div>
                            <div class="col-md-6 mb-2">
                                <p class="mb-1 text-muted small">Rol</p>
                                <p id="userRole" class="mb-0">-</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-uppercase text-muted mb-2"><i class="fas fa-map-marker-alt me-2"></i>Dirección</h6>
                        <p id="userAddress" class="mb-1">-</p>
                        <p id="userCity" class="mb-0">-</p>
                    </div>
                    
                    <div>
                        <h6 class="text-uppercase text-muted mb-2"><i class="fas fa-calendar-alt me-2"></i>Información Adicional</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <p class="mb-1 text-muted small">Fecha de Registro</p>
                                <p id="userCreationDate" class="mb-0">-</p>
                            </div>
                            <div class="col-md-6 mb-2">
                                <p class="mb-1 text-muted small">Última Sesión</p>
                                <p id="userLastLogin" class="mb-0">-</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <a href="#" id="editUserBtn" class="btn btn-warning text-white">
                        <i class="fas fa-edit me-1"></i>Editar
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../footer.php'; ?>
</div>

<!-- JavaScript Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar DataTables (desactivado por conflicto con paginación manual)
    // Si prefieres usar DataTables en vez de la paginación manual, descomenta esta línea
    /*
    $('#usuariosTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        }
    });
    */
    
    // Función para mostrar overlay de carga
    function showLoading() {
        document.getElementById('loadingOverlay').style.visibility = 'visible';
        document.getElementById('loadingOverlay').style.opacity = '1';
    }
    
    // Función para ocultar overlay de carga
    function hideLoading() {
        document.getElementById('loadingOverlay').style.visibility = 'hidden';
        document.getElementById('loadingOverlay').style.opacity = '0';
    }
    
    // Botones de eliminar usuario
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede revertir",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading();
                    // Redireccionar a la página de eliminación
                    window.location.href = 'eliminar_usuario.php?id=' + userId;
                }
            });
        });
    });
    
    // Botones de activar usuario
    document.querySelectorAll('.activate-btn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            
            Swal.fire({
                title: 'Activar usuario',
                text: "¿Deseas activar este usuario?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, activar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading();
                    // Redireccionar a la página de activación
                    window.location.href = 'activar_usuario.php?id=' + userId;
                }
            });
        });
    });
    
    // Ver detalles del usuario
    document.querySelectorAll('.view-btn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            
            // Aquí realizarías una petición AJAX para obtener los datos del usuario
            // Simulamos los datos para este ejemplo
            fetch('get_usuario.php?id=' + userId)
                .then(response => response.json())
                .then(data => {
                    // Rellenar datos en el modal
                    document.getElementById('userInitial').textContent = data.nombre.charAt(0).toUpperCase();
                    document.getElementById('userName').textContent = data.nombre + ' ' + data.apellido;
                    document.getElementById('userEmail').textContent = data.email;
                    
                    const statusEl = document.getElementById('userStatus');
                    statusEl.textContent = data.estado.charAt(0).toUpperCase() + data.estado.slice(1);
                    statusEl.className = 'estado-badge';
                    if (data.estado === 'activo') {
                        statusEl.classList.add('estado-activo');
                    } else if (data.estado === 'inactivo') {
                        statusEl.classList.add('estado-inactivo');
                    } else {
                        statusEl.classList.add('estado-pendiente');
                    }
                    
                    document.getElementById('userPhone').textContent = data.telefono || '-';
                    document.getElementById('userBirthdate').textContent = data.fecha_nacimiento ? formatDate(data.fecha_nacimiento) : '-';
                    document.getElementById('userDocument').textContent = data.tipo_documento && data.documento_identidad ? 
                        data.tipo_documento.toUpperCase() + ': ' + data.documento_identidad : '-';
                    document.getElementById('userRole').textContent = data.rol_nombre || '-';
                    document.getElementById('userAddress').textContent = data.direccion || '-';
                    document.getElementById('userCity').textContent = data.ciudad || '-';
                    document.getElementById('userCreationDate').textContent = formatDate(data.fecha_creacion);
                    document.getElementById('userLastLogin').textContent = data.ultima_sesion ? formatDate(data.ultima_sesion) : '-';
                    
                    // Configurar botón de editar
                    document.getElementById('editUserBtn').href = 'editar_usuario.php?id=' + userId;
                    
                    // Mostrar modal
                    const modal = new bootstrap.Modal(document.getElementById('verUsuarioModal'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error al obtener datos del usuario:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudieron cargar los datos del usuario',
                        icon: 'error'
                    });
                });
        });
    });
    
    // Validación del formulario de nuevo usuario
    const nuevoUsuarioForm = document.getElementById('nuevoUsuarioForm');
    if (nuevoUsuarioForm) {
        nuevoUsuarioForm.addEventListener('submit', function(event) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                event.preventDefault();
                Swal.fire({
                    title: 'Error',
                    text: 'Las contraseñas no coinciden',
                    icon: 'error'
                });
            } else {
                showLoading();
            }
        });
    }
    
    // Función para formatear fechas
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }
    
    // Buscar al presionar Enter en el campo de búsqueda
    const searchInput = document.querySelector('.search-container input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.target.form.submit();
            }
        });
    }
    
    // Mostrar mensaje de éxito si existe el parámetro en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const successMsg = urlParams.get('success');
    const errorMsg = urlParams.get('error');
    
    if (successMsg) {
        Swal.fire({
            title: '¡Éxito!',
            text: decodeURIComponent(successMsg),
            icon: 'success',
            timer: 3000,
            timerProgressBar: true
        });
    } else if (errorMsg) {
        Swal.fire({
            title: 'Error',
            text: decodeURIComponent(errorMsg),
            icon: 'error'
        });
    }
    
    // Gráfica de estadísticas de usuarios (opcional)
    const ctx = document.getElementById('userStatsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Activos', 'Inactivos', 'Pendientes'],
                datasets: [{
                    data: [
                        <?= $stats['usuarios_activos'] ?>, 
                        <?= $stats['usuarios_inactivos'] ?>, 
                        <?= $stats['usuarios_pendientes'] ?>
                    ],
                    backgroundColor: [
                        '#1cc88a',
                        '#e74a3b',
                        '#f6c23e'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
});
</script>
</body>
</html>