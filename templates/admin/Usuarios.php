<?php
// Configuración de la página
$current_page = 'Usuarios';
$page_title = "Gestión de Usuarios";
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => '/ourcenter/templates/dashboard.php'],
    ['title' => 'Usuarios']
];

// Incluir el header primero (antes de cualquier salida HTML)
include_once '../header.php';

// Procesar datos después del header
require_once '../../config/db.php';


// Parámetros de paginación
$registros_por_pagina = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina - 1) * $registros_por_pagina;

// Búsqueda
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$where = "WHERE rol_id = 2";
$params = [];

if (!empty($busqueda)) {
    $where .= " AND (nombre LIKE ? OR email LIKE ? OR apellido LIKE ?)";
    $params = ["%$busqueda%", "%$busqueda%", "%$busqueda%"];
}

// Contar total de registros
$sql_total = "SELECT COUNT(*) FROM usuarios " . $where;
$stmt_total = $pdo->prepare($sql_total);
$stmt_total->execute($params);
$total_registros = $stmt_total->fetchColumn();
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener estudiantes
$stmt = $pdo->prepare("
    SELECT 
        u.id, 
        u.nombre, 
        u.apellido, 
        u.email, 
        u.telefono, 
        u.fecha_creacion,
        u.rol_id,
        u.estado,
        CASE 
            WHEN u.rol_id = 1 THEN 'Administrador'
            WHEN u.rol_id = 2 THEN 'Estudiante'
            WHEN u.rol_id = 3 THEN 'Profesor'
            ELSE 'Desconocido'
        END AS rol_nombre,
        COUNT(i.id) AS cursos_inscritos
    FROM usuarios u
    LEFT JOIN inscripciones i ON u.id = i.usuario_id
    GROUP BY u.id
    ORDER BY u.fecha_creacion DESC
    LIMIT ? OFFSET ?
");


$all_params = array_merge($params, [$registros_por_pagina, $offset]);
$stmt->execute($all_params);
$usuarios = $stmt->fetchAll();

// Obtener roles para filtrar
$stmt_roles = $pdo->query("SELECT id, nombre FROM roles ORDER BY nombre");
$roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

// CSS específico para esta página
$page_css = [
    'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css'
];

// Scripts específicos para esta página
$page_head_scripts = [
    'https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js'
];
?>

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
    <?php include '../preloader.php'; ?>
    
    <!-- Sidebar Toggle Button -->
    <div class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </div>

   <?php 
   // Estadísticas básicas
$stmt_stats = $pdo->query("SELECT 
COUNT(*) as total_usuarios,
SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as usuarios_activos,
SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as usuarios_inactivos,
SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as usuarios_pendientes
FROM usuarios");

            if ($stmt_stats) {
                $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
            } else {
                // Manejo de error
                die("Error en la consulta SQL: " . implode(" ", $pdo->errorInfo()));
            }
            ?>
            
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
                    <div class="avatar-preview mx-auto mb-3"
                        style="width: 100px; height: 100px; background-color: #f0f2f5; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: bold; color: #4e73df;">
                        <span id="userInitial"></span>
                    </div>
                    <h5 id="userName" class="mb-0"></h5>
                    <p id="userEmail" class="text-muted"></p>
                    <span id="userStatus" class="estado-badge"></span>
                </div>

                <div class="mb-3">
                    <h6 class="text-uppercase text-muted mb-2">
                        <i class="fas fa-id-card me-2"></i>Información Personal
                    </h6>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <p class="mb-1 text-muted small">Documento</p>
                            <p id="userDocument" class="mb-0">-</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <p class="mb-1 text-muted small">Rol</p>
                            <p id="userRole" class="mb-0">-</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <p class="mb-1 text-muted small">Teléfono</p>
                            <p id="userPhone" class="mb-0">-</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <p class="mb-1 text-muted small">Fecha de Nacimiento</p>
                            <p id="userBirthdate" class="mb-0">-</p>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <h6 class="text-uppercase text-muted mb-2">
                        <i class="fas fa-map-marker-alt me-2"></i>Dirección
                    </h6>
                    <p id="userAddress" class="mb-1">-</p>
                    <p id="userCity" class="mb-0">-</p>
                </div>

                <div>
                    <h6 class="text-uppercase text-muted mb-2">
                        <i class="fas fa-calendar-alt me-2"></i>Información Adicional
                    </h6>
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

    <!-- Scripts principales -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    
    <!-- Scripts específicos de la página -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

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
            fetch('/ourcenter/templates/admin/actions/get_usuario.php?id=' + userId)
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

    <!-- Scripts específicos de la página -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <?php include '../footer.php'; ?>
</body>
</html>