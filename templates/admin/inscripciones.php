<?php include '../preloader.php';?>
<?php include '../header.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inscripciones - Our Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    
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
            background: #0a1b5c;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
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
        
        .estado-activa { background-color: #28a745; color: white; }
        .estado-pendiente { background-color: #ffc107; color: #212529; }
        .estado-cancelada { background-color: #dc3545; color: white; }
        .estado-completa { background-color: #17a2b8; color: white; }
        .pago-pendiente { background-color: #ffc107; color: #212529; }
        .pago-completado { background-color: #28a745; color: white; }
        .pago-nulo { background-color: #6c757d; color: white; }
        
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
        
        .btn-sm {
            border-radius: 20px;
            padding: 0.25rem 0.7rem;
        }
        
        .inscripcion-details:hover {
            cursor: pointer;
            background-color: rgba(10, 27, 92, 0.05);
        }
        
        .select2-container--bootstrap-5 .select2-selection {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
            min-height: 38px;
        }
        
        .estudiante-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            background: #f8f9fa;
        }
        
        .estudiante-card.selected {
            border-color: #0a1b5c;
            background-color: rgba(10, 27, 92, 0.1);
        }
        
        .curso-info {
            background: #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .salon-badge {
            background: #0a1b5c;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
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

    <!-- Sidebar Toggle Button -->
    <div class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </div>
    <div class="container-fluid">
        <div class="page-content">
            <!-- Mensajes de confirmación -->
            <div id="mensajes-container"></div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-user-graduate me-2"></i> Gestión de Inscripciones</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaInscripcion">
                    <i class="fas fa-plus me-2"></i>Nueva Inscripción
                </button>
            </div>

            <!-- Resumen estadístico -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="dashboard-stats">
                        <div class="stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="stat-number">125</div>
                        <div class="stat-label">Total Inscripciones</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number">98</div>
                        <div class="stat-label">Inscripciones Activas</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number">15</div>
                        <div class="stat-label">Inscripciones Pendientes</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-stats">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-number">7</div>
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
                                <!-- Datos de ejemplo -->
                                <tr class="inscripcion-details" data-id="1">
                                    <td>1</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="inscripcion-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">María García</div>
                                                <small class="text-muted">maria@email.com</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="inscripcion-curso">
                                            <img src="https://via.placeholder.com/40x40/0a1b5c/ffffff?text=C" alt="Curso">
                                            <div>Desarrollo Web Frontend</div>
                                        </div>
                                    </td>
                                    <td>15/01/2025</td>
                                    <td><span class="badge estado-activa">Activa</span></td>
                                    <td><span class="badge pago-completado">Pagado</span></td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary ver-detalles" data-id="1">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning editar-inscripcion" data-id="1">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger eliminar-inscripcion" data-id="1">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="inscripcion-details" data-id="2">
                                    <td>2</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="inscripcion-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">Carlos López</div>
                                                <small class="text-muted">carlos@email.com</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="inscripcion-curso">
                                            <img src="https://via.placeholder.com/40x40/1a3ba0/ffffff?text=P" alt="Curso">
                                            <div>Python para Principiantes</div>
                                        </div>
                                    </td>
                                    <td>12/01/2025</td>
                                    <td><span class="badge estado-pendiente">Pendiente</span></td>
                                    <td><span class="badge pago-pendiente">Pendiente</span></td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary ver-detalles" data-id="2">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning editar-inscripcion" data-id="2">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger eliminar-inscripcion" data-id="2">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Botón flotante para añadir nueva inscripción -->
            <button type="button" class="btn btn-primary btn-floating" data-bs-toggle="modal" data-bs-target="#modalNuevaInscripcion">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>

    <!-- Modal Nueva Inscripción -->
    <div class="modal fade" id="modalNuevaInscripcion" tabindex="-1" aria-labelledby="modalNuevaInscripcionLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevaInscripcionLabel">
                        <i class="fas fa-plus me-2"></i>Nueva Inscripción
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formNuevaInscripcion">
                    <div class="modal-body">
                        <div class="row">
                            <!-- Selección de Curso -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-book me-2"></i>Seleccionar Curso</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="curso-select" class="form-label">Curso <span class="text-danger">*</span></label>
                                            <select class="form-select" id="curso-select" name="curso_id" required>
                                                <option value="">Selecciona un Salon...</option>
                                                        <!-- Las opciones se cargarán dinámicamente -->
                                            </select>
                                        </div>
                                        
                                        <div id="curso-info" class="curso-info" style="display: none;">
                                            <div class="row">
                                                <div class="col-6">
                                                    <small class="text-muted">Salón asignado:</small>
                                                    <div><span id="curso-salon" class="salon-badge"></span></div>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Precio:</small>
                                                    <div class="fw-bold text-success">$<span id="curso-precio">0</span></div>
                                                </div>
                                                <div class="col-12 mt-2">
                                                    <small class="text-muted">Duración:</small>
                                                    <div><span id="curso-duracion"></span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Selección de Estudiantes -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="fas fa-users me-2"></i>Seleccionar Estudiantes</h6>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoEstudiante">
                                            <i class="fas fa-user-plus"></i> Nuevo
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <input type="text" class="form-control" id="buscar-estudiantes" placeholder="Buscar estudiantes...">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="seleccionar-todos">
                                                <label class="form-check-label fw-bold" for="seleccionar-todos">
                                                    Seleccionar todos
                                                </label>
                                            </div>
                                        </div>

                                        <div id="lista-estudiantes" style="max-height: 300px; overflow-y: auto;">
                                            <!-- Lista de estudiantes -->
                                            <div class="estudiante-card" data-id="1">
                                                <div class="form-check">
                                                    <input class="form-check-input estudiante-checkbox" type="checkbox" value="1" id="estudiante-1">
                                                    <label class="form-check-label w-100" for="estudiante-1">
                                                        <div class="d-flex align-items-center">
                                                            <div class="inscripcion-avatar me-2">
                                                                <i class="fas fa-user"></i>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold">María García Rodríguez</div>
                                                                <small class="text-muted">maria.garcia@email.com</small>
                                                                <br><small class="text-muted">Tel: +57 300 123 4567</small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="estudiante-card" data-id="2">
                                                <div class="form-check">
                                                    <input class="form-check-input estudiante-checkbox" type="checkbox" value="2" id="estudiante-2">
                                                    <label class="form-check-label w-100" for="estudiante-2">
                                                        <div class="d-flex align-items-center">
                                                            <div class="inscripcion-avatar me-2">
                                                                <i class="fas fa-user"></i>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold">Carlos López Martínez</div>
                                                                <small class="text-muted">carlos.lopez@email.com</small>
                                                                <br><small class="text-muted">Tel: +57 310 987 6543</small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="estudiante-card" data-id="3">
                                                <div class="form-check">
                                                    <input class="form-check-input estudiante-checkbox" type="checkbox" value="3" id="estudiante-3">
                                                    <label class="form-check-label w-100" for="estudiante-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="inscripcion-avatar me-2">
                                                                <i class="fas fa-user"></i>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold">Ana Sofía Herrera</div>
                                                                <small class="text-muted">ana.herrera@email.com</small>
                                                                <br><small class="text-muted">Tel: +57 320 456 7890</small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <small class="text-muted">Estudiantes seleccionados: <span id="contador-seleccionados">0</span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Opciones adicionales -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Opciones de Inscripción</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="estado-inscripcion" class="form-label">Estado inicial</label>
                                            <select class="form-select" id="estado-inscripcion" name="estado">
                                                <option value="Activa">Activa</option>
                                                <option value="Pendiente" selected>Pendiente</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="metodo-inscripcion" class="form-label">Método de inscripción</label>
                                            <select class="form-select" id="metodo-inscripcion" name="metodo_inscripcion">
                                                <option value="Presencial">Presencial</option>
                                                <option value="Online" selected>Online</option>
                                                <option value="Telefónica">Telefónica</option>
                                            </select>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="enviar-notificacion" checked>
                                            <label class="form-check-label" for="enviar-notificacion">
                                                Enviar notificación por email
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Resumen de Inscripción</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="resumen-inscripcion">
                                            <div class="text-center text-muted">
                                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                                <p>Selecciona un curso y estudiantes para ver el resumen</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notas adicionales -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="notas-inscripcion" class="form-label">Notas adicionales</label>
                                    <textarea class="form-control" id="notas-inscripcion" name="notas" rows="3" placeholder="Escribe cualquier información adicional sobre esta inscripción..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btn-crear-inscripcion" disabled>
                            <i class="fas fa-save me-2"></i>Crear Inscripciones
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Estudiante -->
    <div class="modal fade" id="modalNuevoEstudiante" tabindex="-1" aria-labelledby="modalNuevoEstudianteLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoEstudianteLabel">
                        <i class="fas fa-user-plus me-2"></i>Nuevo Estudiante
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formNuevoEstudiante">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nuevo-nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nuevo-nombre" name="nombre" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nuevo-apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nuevo-apellido" name="apellido" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nuevo-email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="nuevo-email" name="email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nuevo-telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="nuevo-telefono" name="telefono">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="nuevo-documento" class="form-label">Documento de Identidad</label>
                            <input type="text" class="form-control" id="nuevo-documento" name="documento">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Crear Estudiante
                        </button>
                    </div>
                </form>
            </div>
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
                                        <button class="btn btn-success btn-sm" id="modal-registrar-pago">Registrar Pago</button>
                                        <button class="btn btn-info btn-sm" id="modal-ver-pagos">Ver Pagos</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h5>Acciones rápidas</h5>
                        <button class="btn btn-warning" id="modal-editar-link"><i class="fas fa-edit me-2"></i>Editar Inscripción</button>
                        <button class="btn btn-primary" id="modal-generar-certificado-link"><i class="fas fa-certificate me-2"></i>Generar Certificado</button>
                        <button class="btn btn-info" id="modal-enviar-recordatorio-link"><i class="fas fa-envelope me-2"></i>Enviar Recordatorio</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

     <!-- Scripts necesarios -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Script personalizado -->
    <script src="../../js/inscripciones.js"></script>
</body>
</html>