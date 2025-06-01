<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');

// inscripciones.php
require_once '../../config/db.php';

// Obtener lista de inscripciones con más información
$stmt = $pdo->query("
    SELECT i.id, 
           u.nombre AS estudiante, 
           u.email AS email_estudiante, 
           u.telefono AS telefono_estudiante,
           c.nombre AS curso, 
           c.imagen_url AS curso_imagen, 
           i.estado, 
           i.metodo_inscripcion,
           i.fecha_creacion AS fecha_inscripcion, 
           i.fecha_actualizacion AS ultima_actualizacion, 
           p.estado AS estado_pago,
           p.monto AS monto_pago
    FROM inscripciones i 
    JOIN usuarios u ON i.usuario_id = u.id 
    JOIN cursos c ON i.curso_id = c.id
    LEFT JOIN pagos p ON i.id = p.inscripcion_id
    ORDER BY i.fecha_creacion DESC
");

$inscripciones = $stmt->fetchAll();

// Obtener estadísticas
$stmt = $pdo->query("SELECT COUNT(*) as total FROM inscripciones");
$totalInscripciones = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as activas FROM inscripciones WHERE estado = 'Activa'");
$inscripcionesActivas = $stmt->fetch()['activas'];

$stmt = $pdo->query("SELECT COUNT(*) as pendientes FROM inscripciones WHERE estado = 'Pendiente'");
$inscripcionesPendientes = $stmt->fetch()['pendientes'];

$stmt = $pdo->query("
    SELECT COUNT(*) as pagos_pendientes 
    FROM inscripciones i 
    LEFT JOIN pagos p ON i.id = p.inscripcion_id 
    WHERE p.estado = 'Pendiente'
");
$pagosPendientes = $stmt->fetch()['pagos_pendientes'];

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
    <title>Gestión de Inscripciones - Our Center</title>
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
    <!-- <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css"> -->
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    
    <style>
        .dashboard-stats {
            background: linear-gradient(135deg, #0a1b5c, #1a3ba0);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .dashboard-stats:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.15);
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
        
        .card {
            border-radius: 10px;
            overflow: hidden;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .page-content {
            padding: 30px;
            background-color: #f8f9fa;
            min-height: calc(100vh - 60px);
        }
        
        .inscripcion-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
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
        
        .card-header {
            background-color: rgba(10, 27, 92, 0.05);
            border-bottom: none;
            padding: 15px 20px;
        }
        
        .estado-activa {
            background-color: #28a745;
            color: white;
        }
        
        .estado-pendiente {
            background-color: #ffc107;
            color: #212529;
        }
        
        .estado-cancelada {
            background-color: #dc3545;
            color: white;
        }
        
        .estado-completa {
            background-color: #17a2b8;
            color: white;
        }
        
        .pago-pendiente {
            background-color: #ffc107;
            color: #212529;
        }
        
        .pago-completado {
            background-color: #28a745;
            color: white;
        }
        
        .pago-nulo {
            background-color: #6c757d;
            color: white;
        }
        
        .filter-section {
            background-color: #fff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .inscripcion-curso {
            display: flex;
            align-items: center;
        }
        
        .inscripcion-curso img {
            width: 40px;
            height: 40px;
            border-radius: 5px;
            margin-right: 10px;
            object-fit: cover;
        }
        
        .table th {
            border-top: none;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 20px;
            border: 1px solid #ced4da;
            padding: 5px 10px;
            margin-left: 10px;
        }
        
        .dataTables_wrapper .dataTables_length select {
            border-radius: 10px;
            border: 1px solid #ced4da;
            padding: 5px 10px;
        }
        
        .btn-sm {
            border-radius: 20px;
            padding: 0.25rem 0.7rem;
        }
        
        .dataTables_info {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .pagination .page-item .page-link {
            border-radius: 5px;
            margin: 0 2px;
            color: #0a1b5c;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #0a1b5c;
            border-color: #0a1b5c;
        }
        
        .inscripcion-details {
            transition: all 0.3s;
        }
        
        .inscripcion-details:hover {
            cursor: pointer;
            background-color: rgba(10, 27, 92, 0.05);
        }
        
        @media (max-width: 768px) {
            .dashboard-stats {
                margin-bottom: 15px;
            }
            
            .page-content {
                padding: 10px;
            }
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
                <h1><i class="fas fa-user-graduate me-2"></i> Gestión de Inscripciones</h1>
                <a href="nueva_inscripcion.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Nueva Inscripción</a>
            </div>

            <!-- Resumen estadístico -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="dashboard-stats">
                        <div class="stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="stat-number"><?= $totalInscripciones ?></div>
                        <div class="stat-label">Total Inscripciones</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number"><?= $inscripcionesActivas ?></div>
                        <div class="stat-label">Inscripciones Activas</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number"><?= $inscripcionesPendientes ?></div>
                        <div class="stat-label">Inscripciones Pendientes</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-number"><?= $pagosPendientes ?></div>
                        <div class="stat-label">Pagos Pendientes</div>
                    </div>
                </div>
            </div>

            <!-- Filtros y opciones de búsqueda -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="filtro-global" placeholder="Buscar inscripciones...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select mb-3" id="filtro-estado">
                                <option value="">Todos los estados</option>
                                <option value="Activa">Activa</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Cancelada">Cancelada</option>
                                <option value="Completa">Completa</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select mb-3" id="filtro-pago">
                                <option value="">Todos los pagos</option>
                                <option value="Completado">Pagado</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="null">Sin pago</option>
                            </select>
                        </div>
                        <div class="col-md-2 text-end">
                            <button class="btn btn-outline-secondary mb-3" id="reset-filtros">
                                <i class="fas fa-sync-alt me-1"></i> Reiniciar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de inscripciones -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Inscripciones</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tabla-inscripciones">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Estudiante</th>
                                    <th>Curso</th>
                                    <th>Fecha Inscripción</th>
                                    <th>Estado</th>
                                    <th>Pago</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inscripciones as $inscripcion): ?>
                                    <tr class="inscripcion-details" data-id="<?= $inscripcion['id'] ?>">
                                        <td><?= $inscripcion['id'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="inscripcion-avatar bg-light d-flex align-items-center justify-content-center text-primary">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($inscripcion['estudiante']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($inscripcion['email_estudiante']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="inscripcion-curso">
                                                <img src="../.<?= !empty($inscripcion['curso_imagen']) ? htmlspecialchars($inscripcion['curso_imagen']) : '../../images/cursos/default-course.jpg' ?>" alt="<?= htmlspecialchars($inscripcion['curso']) ?>">
                                                <div><?= htmlspecialchars($inscripcion['curso']) ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <?= !empty($inscripcion['fecha_inscripcion']) ? date('d/m/Y', strtotime($inscripcion['fecha_inscripcion'])) : 'N/A' ?>
                                        </td>
                                        <td>
                                            <?php if ($inscripcion['estado'] == 'Activa'): ?>
                                                <span class="badge estado-activa">Activa</span>
                                            <?php elseif ($inscripcion['estado'] == 'Pendiente'): ?>
                                                <span class="badge estado-pendiente">Pendiente</span>
                                            <?php elseif ($inscripcion['estado'] == 'Cancelada'): ?>
                                                <span class="badge estado-cancelada">Cancelada</span>
                                            <?php elseif ($inscripcion['estado'] == 'Completa'): ?>
                                                <span class="badge estado-completa">Completa</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($inscripcion['estado']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (empty($inscripcion['estado_pago'])): ?>
                                                <span class="badge pago-nulo">Sin pago</span>
                                            <?php elseif ($inscripcion['estado_pago'] == 'Completado'): ?>
                                                <span class="badge pago-completado">Pagado</span>
                                            <?php elseif ($inscripcion['estado_pago'] == 'Pendiente'): ?>
                                                <span class="badge pago-pendiente">Pendiente</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($inscripcion['estado_pago']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-primary ver-detalles" data-id="<?= $inscripcion['id'] ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="editar_inscripcion.php?id=<?= $inscripcion['id'] ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger eliminar-inscripcion" data-id="<?= $inscripcion['id'] ?>" data-estudiante="<?= htmlspecialchars($inscripcion['estudiante']) ?>">
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
            
            <!-- Botón flotante para añadir nueva inscripción -->
            <a href="nueva_inscripcion.php" class="btn btn-primary btn-floating">
                <i class="fas fa-plus" style="margin-top: 15px;"></i>
            </a>
        </div>
    </div>

    <!-- Modal de detalles de inscripción -->
    <div class="modal fade" id="modalDetallesInscripcion" tabindex="-1" aria-labelledby="modalDetallesInscripcionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetallesInscripcionLabel">Detalles de la Inscripción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Información del Estudiante</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Nombre:</strong> <span id="modal-estudiante-nombre"></span></p>
                                    <p><strong>Email:</strong> <span id="modal-estudiante-email"></span></p>
                                    <p><strong>Teléfono:</strong> <span id="modal-estudiante-telefono"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-book me-2"></i>Información del Curso</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Curso:</strong> <span id="modal-curso-nombre"></span></p>
                                    <p><strong>Fecha de Inscripción:</strong> <span id="modal-fecha-inscripcion"></span></p>
                                    <p><strong>Última Actualización:</strong> <span id="modal-ultima-actualizacion"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Estado</h6>
                                </div>
                                <div class="card-body">
                                    <h5><span id="modal-estado-badge" class="badge"></span></h5>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-sm btn-outline-success cambiar-estado" data-estado="Activa">Activar</button>
                                        <button class="btn btn-sm btn-outline-warning cambiar-estado" data-estado="Pendiente">Pendiente</button>
                                        <button class="btn btn-sm btn-outline-danger cambiar-estado" data-estado="Cancelada">Cancelar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>Pago</h6>
                                </div>
                                <div class="card-body">
                                    <h5><span id="modal-pago-badge" class="badge"></span></h5>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <a href="#" id="modal-registrar-pago" class="btn btn-success">Registrar Pago</a>
                                        <a href="#" id="modal-ver-pagos" class="btn btn-info">Ver Pagos</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h5>Acciones rápidas</h5>
                        <a href="#" id="modal-editar-link" class="btn btn-warning"><i class="fas fa-edit me-2"></i>Editar Inscripción</a>
                        <a href="#" id="modal-generar-certificado-link" class="btn btn-primary"><i class="fas fa-certificate me-2"></i>Generar Certificado</a>
                        <a href="#" id="modal-enviar-recordatorio-link" class="btn btn-info"><i class="fas fa-envelope me-2"></i>Enviar Recordatorio</a>
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
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            $('#tablaInscripciones').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });

            // Cerrar alertas laterales
            document.getElementById('closeAlertBox').addEventListener('click', function () {
                document.getElementById('alertBox').style.display = 'none';
            });
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
</body>
</html>
