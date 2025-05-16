<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');

// cursos.php
require_once '../../config/db.php';
$current_page = 'Cursos';

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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cursos - Our Center</title>
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
        :root {
    --primary-color: #0a1b5c;
    --primary-color-light: #122c8e;
    --secondary-color: #122c8e;
    --accent-color: #e74c3c;
    --success-color: #28a745;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --light-bg: #f8f9fa;
    --dark-bg: #343a40;
    --border-color: #dee2e6;
    --text-muted: #6c757d;
}

        @media (min-width: 620px) {
    .col-lg-3 {
        flex: 0 0 auto;
        width: 50% !important;
    }
}
@media (min-width: 768px) {
    .col-lg-3 {
        flex: 0 0 auto;
        width: 33.3% !important;
    }
}
        .card-curso {
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
         .curso-imagen:hover {
            transform: scale(1.05);
        }
        
        .card-curso:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        
        .curso-imagen {
            object-fit: cover;
            width: 100%;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            transition: transform 0.3s;
        }
        
        .badge-curso {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        
        .card-footer {
            background-color: rgba(10, 27, 92, 0.05);
            border-top: none;
        }
        
        .dashboard-stats {
            background: linear-gradient(135deg,rgb(60, 58, 58),rgb(154, 146, 146));
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .stat-label {
            opacity: 0.8;
        }
        
        .curso-acciones {
            visibility: hidden;
            opacity: 0;
            transition: visibility 0s, opacity 0.3s linear;
            position: absolute;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            background-color: rgba(255,255,255,0.9);
            padding: 10px;
        }
        
        .card-curso:hover .curso-acciones {
            visibility: visible;
            opacity: 1;
        }
        
        .card-title {
            color: #0a1b5c;
            font-weight: 600;
        }
        
        .vista-toggle {
            margin-bottom: 20px;
        }
        
        .vista-toggle .btn {
            border-radius: 20px;
        }
        
        .filter-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 20px;
            border: 1px solid #ced4da;
            padding: 5px 10px;
        }
        
        .page-content {
            padding: 30px;
            background-color: #f8f9fa;
            min-height: calc(100vh - 60px);
        }
        
        .container-fluid {
            padding: 0;
        }
        
        .nav-stats {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .nav-stats .nav-link {
            padding: 15px;
            color: #495057;
        }
        
        .nav-stats .nav-link.active {
            background-color: #0a1b5c;
            color: white;
        }
        
        .estado-activo {
            background-color: #28a745;
            color: white;
        }
        
        .estado-inactivo {
            background-color: #dc3545;
            color: white;
        }
        
        .estado-pendiente {
            background-color: #ffc107;
            color: #212529;
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
        
        .precio-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: rgba(10, 27, 92, 0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
        }
        
        .card-curso .card-body {
            position: relative;
            z-index: 1;
        }
        
        .inscritos-badge {
            display: inline-block;
            margin-right: 10px;
        }
        
        @media (max-width: 768px) {
            .dashboard-stats {
                margin-bottom: 15px;
            }
            
            
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
                <div class="logo-text" style="margin-left: 10px;">OUR CENTER</div>
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
                <a class="nav-link" href="/ourcenter/templates/admin/Usuarios.php">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Usuarios</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="/ourcenter/templates/admin/cursos.php">
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
                <h1><i class="fas fa-book me-2"></i> Gestión de Cursos</h1>
                <a href="nuevo_curso.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Nuevo Curso</a>
            </div>

            <!-- Resumen estadístico -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="dashboard-stats">
                        <div class="stat-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-number"><?= count($cursos) ?></div>
                        <div class="stat-label">Total de Cursos</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats">
                        <div class="stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="stat-number">
                            <?php
                            $totalInscritos = 0;
                            foreach ($cursos as $curso) {
                                $totalInscritos += $curso['num_inscritos'];
                            }
                            echo $totalInscritos;
                            ?>
                        </div>
                        <div class="stat-label">Total de Estudiantes</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number">
                            <?php
                            $cursosActivos = 0;
                            foreach ($cursos as $curso) {
                                if ($curso['estado'] == 'Activo') {
                                    $cursosActivos++;
                                }
                            }
                            echo $cursosActivos;
                            ?>
                        </div>
                        <div class="stat-label">Cursos Activos</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-number">
                            <?php
                            $cursosPendientes = 0;
                            foreach ($cursos as $curso) {
                                if ($curso['estado'] == 'Pendiente') {
                                    $cursosPendientes++;
                                }
                            }
                            echo $cursosPendientes;
                            ?>
                        </div>
                        <div class="stat-label">Próximos Cursos</div>
                    </div>
                </div>
            </div>

            <!-- Filtros y opciones de visualización -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="filtro-cursos" placeholder="Buscar cursos..." aria-label="Buscar cursos">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="filtro-estado">
                                <option value="">Todos los estados</option>
                                <option value="Activo">Activo</option>
                                <option value="Inactivo">Inactivo</option>
                                <option value="Pendiente">Pendiente</option>
                            </select>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group" role="group" aria-label="Vista de cursos">
                                <button type="button" class="btn btn-outline-primary active" id="vista-cards"><i class="fas fa-th-large"></i></button>
                                <button type="button" class="btn btn-outline-primary" id="vista-tabla"><i class="fas fa-list"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<!-- Vista de Tarjetas -->
<div class="row" id="vista-cards-container">
    <?php foreach ($cursos as $curso): ?>
        <div class="col-md-4 col-lg-3 mb-4 curso-item" data-estado="<?= htmlspecialchars($curso['estado'] ?? 'Activo') ?>">
            <div class="card card-curso h-100 d-flex flex-column">
                <div class="position-relative">
                    <img src="../.<?= !empty($curso['imagen_url']) ? htmlspecialchars($curso['imagen_url']) : '../../images/cursos/default-course.jpg' ?>" class="card-img-top curso-imagen" alt="<?= htmlspecialchars($curso['nombre']) ?>">
                    <div class="precio-badge position-absolute top-0 start-0 bg-primary text-white p-1 rounded-end">$<?= number_format($curso['precio'] ?? 0, 2) ?></div>
                    <div class="badge-curso position-absolute top-0 end-0 p-1">
                        <?php if ($curso['estado'] == 'Activo'): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php elseif ($curso['estado'] == 'Inactivo'): ?>
                            <span class="badge bg-secondary">Inactivo</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Pendiente</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body d-flex flex-column flex-grow-1">
                    <h5 class="card-title"><?= htmlspecialchars($curso['nombre']) ?></h5>
                    <p class="card-text text-truncate"><?= htmlspecialchars($curso['descripcion']) ?></p>
                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <span class="badge bg-secondary"><i class="fas fa-user-graduate me-1"></i> <?= $curso['num_inscritos'] ?></span>
                        <?php if (!empty($curso['fecha_inicio'])): ?>
                            <small class="text-muted"><i class="fas fa-calendar-alt me-1"></i> <?= date('d/m/Y', strtotime($curso['fecha_inicio'])) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted"><?= !empty($curso['duracion_horas']) ? $curso['duracion_horas'] . ' semanas' : 'Duración no especificada' ?></small>
                    <div class="d-flex" style="justify-content: space-between !important;">
                        <button type="button" class="btn btn-sm btn-primary me-1 ver-detalles" data-id="<?= $curso['id'] ?>">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="editar_curso.php?id=<?= $curso['id'] ?>" class="btn btn-sm btn-warning me-1">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger eliminar-curso" data-id="<?= $curso['id'] ?>" data-nombre="<?= htmlspecialchars($curso['nombre']) ?>">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
                <?php if (count($cursos) == 0): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-book fa-3x text-muted mb-3"></i>
                        <h3 class="text-muted">No hay cursos disponibles</h3>
                        <p>Comienza añadiendo un nuevo curso.</p>
                        <a href="nuevo_curso.php" class="btn btn-primary mt-2"><i class="fas fa-plus me-2"></i>Crear Curso</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Vista de Tabla (inicialmente oculta) -->
            <div class="card" id="vista-tabla-container" style="display: none;">
                <div class="card-body">
                    <table class="table table-striped" id="tabla-cursos">
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
                            <?php foreach ($cursos as $curso): ?>
                                <tr>
                                    <td><?= $curso['id'] ?></td>
                                    <td><?= htmlspecialchars($curso['nombre']) ?></td>
                                    <td><?= strlen($curso['descripcion']) > 50 ? htmlspecialchars(substr($curso['descripcion'], 0, 50) . '...') : htmlspecialchars($curso['descripcion']) ?></td>
                                    <td><?= $curso['num_inscritos'] ?></td>
                                    <td><?= !empty($curso['fecha_inicio']) ? date('d/m/Y', strtotime($curso['fecha_inicio'])) : 'No definida' ?></td>
                                    <td>$<?= number_format($curso['precio'] ?? 0, 2) ?></td>
                                    <td>
                                        <?php if ($curso['estado'] == 'Activo'): ?>
                                            <span class="badge estado-activo">Activo</span>
                                        <?php elseif ($curso['estado'] == 'Inactivo'): ?>
                                            <span class="badge estado-inactivo">Inactivo</span>
                                        <?php else: ?>
                                            <span class="badge estado-pendiente">Pendiente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary ver-detalles-tabla" data-id="<?= $curso['id'] ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="editar_curso.php?id=<?= $curso['id'] ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger eliminar-curso-tabla" data-id="<?= $curso['id'] ?>" data-nombre="<?= htmlspecialchars($curso['nombre']) ?>">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Botón flotante para añadir nuevo curso -->
            <a href="nuevo_curso.php" class="btn btn-primary btn-floating">
                <i class="fas fa-plus" style="margin-top: 15px;"></i>
            </a>
        </div>
    </div>

    <!-- Modal de detalles del curso -->
    <div class="modal fade" id="modalDetallesCurso" tabindex="-1" aria-labelledby="modalDetallesCursoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetallesCursoLabel">Detalles del Curso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="" id="modal-curso-imagen" class="img-fluid rounded" alt="Imagen del curso">
                        </div>
                        <div class="col-md-8">
                            <h3 id="modal-curso-nombre"></h3>
                            <p id="modal-curso-descripcion"></p>
                            
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <p><strong>Fecha de Inicio:</strong> <span id="modal-curso-fecha"></span></p>
                                    <p><strong>Duración:</strong> <span id="modal-curso-duracion"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Precio:</strong> <span id="modal-curso-precio"></span></p>
                                    <p><strong>Estudiantes Inscritos:</strong> <span id="modal-curso-inscritos"></span></p>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h5>Acciones rápidas</h5>
                                <a href="#" id="modal-editar-link" class="btn btn-warning"><i class="fas fa-edit me-2"></i>Editar Curso</a>
                                <a href="#" id="modal-inscritos-link" class="btn btn-info"><i class="fas fa-user-graduate me-2"></i>Ver Inscritos</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
        <?php include '../footer.php'; ?>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script> -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            $('#tabla-cursos').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                },
                responsive: true
            });
            
            // Toggle entre vista de tarjetas y tabla
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
            
            // Filtro de búsqueda para vista de tarjetas
            $('#filtro-cursos').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('.curso-item').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
            
            // Filtro por estado
            $('#filtro-estado').on('change', function() {
                var value = $(this).val().toLowerCase();
                if (value === '') {
                    $('.curso-item').show();
                } else {
                    $('.curso-item').hide();
                    $('.curso-item[data-estado="' + value + '"]').show();
                }
            });
            
            // Modal de detalles desde la vista de tarjetas
            $('.ver-detalles').click(function() {
                var cursoId = $(this).data('id');
                // Aquí normalmente harías una solicitud AJAX para obtener los detalles completos
                // Por ahora, usamos los datos que ya tenemos en la página como demostración
                var card = $(this).closest('.card-curso');
                var nombre = card.find('.card-title').text();
                var descripcion = card.find('.card-text').text();
                var imagen = card.find('.curso-imagen').attr('src');
                var precio = card.find('.precio-badge').text();
                var inscritos = card.find('.inscritos-badge').text().trim();
                var fechaTexto = card.find('.text-muted i.fas.fa-calendar-alt').parent().text().trim();
                var duracion = card.find('.card-footer .text-muted').text().trim();
                
                $('#modal-curso-nombre').text(nombre);
                $('#modal-curso-descripcion').text(descripcion);
                $('#modal-curso-imagen').attr('src', imagen);
                $('#modal-curso-precio').text(precio);
                $('#modal-curso-inscritos').text(inscritos);
                $('#modal-curso-fecha').text(fechaTexto || 'No especificada');
                $('#modal-curso-duracion').text(duracion);
                $('#modal-editar-link').attr('href', 'editar_curso.php?id=' + cursoId);
                $('#modal-inscritos-link').attr('href', 'inscritos.php?curso_id=' + cursoId);
                
                $('#modalDetallesCurso').modal('show');
            });
            
            // Modal de detalles desde la vista de tabla
            $('.ver-detalles-tabla').click(function() {
                var cursoId = $(this).data('id');
                var row = $(this).closest('tr');
                var nombre = row.find('td:eq(1)').text();
                var descripcion = row.find('td:eq(2)').text();
                var inscritos = row.find('td:eq(3)').text();
                var fechaTexto = row.find('td:eq(4)').text();
                var precio = row.find('td:eq(5)').text();
                
                // Para la imagen, necesitaríamos obtenerla por AJAX o incluirla en data-attributes
                var imagenDefault = '../../images/cursos/default-course.jpg';
                
                $('#modal-curso-nombre').text(nombre);
                $('#modal-curso-descripcion').text(descripcion);
                $('#modal-curso-imagen').attr('src', imagenDefault);
                $('#modal-curso-precio').text(precio);
                $('#modal-curso-inscritos').text(inscritos);
                $('#modal-curso-fecha').text(fechaTexto);
                $('#modal-curso-duracion').text('Información no disponible en esta vista');
                $('#modal-editar-link').attr('href', 'editar_curso.php?id=' + cursoId);
                $('#modal-inscritos-link').attr('href', 'inscritos.php?curso_id=' + cursoId);
                
                $('#modalDetallesCurso').modal('show');
            });
            
            // Eliminar curso (vista tarjetas)
            $('.eliminar-curso').click(function() {
                var cursoId = $(this).data('id');
                var cursoNombre = $(this).data('nombre');
                
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `¿Realmente deseas eliminar el curso "${cursoNombre}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redireccionar a la página de eliminación
                        window.location.href = `eliminar_curso.php?id=${cursoId}`;
                    }
                });
            });
            
            // Eliminar curso (vista tabla)
            $('.eliminar-curso-tabla').click(function() {
                var cursoId = $(this).data('id');
                var cursoNombre = $(this).data('nombre');
                
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `¿Realmente deseas eliminar el curso "${cursoNombre}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `eliminar_curso.php?id=${cursoId}`;
                    }
                });
            });
            
            // Sidebar toggle functionality
            $('#sidebarToggle').click(function() {
                $('#sidebar').toggleClass('collapsed');
                $('.content-wrapper').toggleClass('expanded');
            });
            
            // Notificaciones toggle
            $('.notification-icon').click(function(e) {
                e.preventDefault();
                $('#alertBox').toggle();
            });
            
            $('#closeAlertBox').click(function() {
                $('#alertBox').hide();
            });
            
            // Cerrar mensajes de alerta automáticamente después de 5 segundos
            setTimeout(function() {
                $('.alert-dismissible').alert('close');
            }, 5000);
        });
    </script>
</body>
</html>