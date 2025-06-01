<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');
require_once '../../config/db.php';

// Verificar si el usuario es administrador
// if (!isset($_SESSION['user_id']) || $_SESSION['rol_id'] != 1) {
//     header('Location: /ourcenter/login.php');
//     exit();
// }

// Procesar acciones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'crear_horario':
            try {
                $stmt = $pdo->prepare("INSERT INTO horarios (curso_id, dia_semana, hora_inicio, hora_fin, salon_id, teacher_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['curso_id'],
                    $_POST['dia_semana'],
                    $_POST['hora_inicio'],
                    $_POST['hora_fin'],
                    $_POST['salon_id'],
                    $_POST['teacher_id']
                ]);
                echo json_encode(['success' => true, 'message' => 'Horario creado exitosamente']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error al crear horario: ' . $e->getMessage()]);
            }
            exit();
            
        case 'actualizar_horario':
            try {
                $stmt = $pdo->prepare("UPDATE horarios SET curso_id = ?, dia_semana = ?, hora_inicio = ?, hora_fin = ?, salon_id = ?, teacher_id = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['curso_id'],
                    $_POST['dia_semana'],
                    $_POST['hora_inicio'],
                    $_POST['hora_fin'],
                    $_POST['salon_id'],
                    $_POST['teacher_id'],
                    $_POST['horario_id']
                ]);
                echo json_encode(['success' => true, 'message' => 'Horario actualizado exitosamente']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar horario: ' . $e->getMessage()]);
            }
            exit();
            
        case 'eliminar_horario':
            try {
                $stmt = $pdo->prepare("DELETE FROM horarios WHERE id = ?");
                $stmt->execute([$_POST['horario_id']]);
                echo json_encode(['success' => true, 'message' => 'Horario eliminado exitosamente']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar horario: ' . $e->getMessage()]);
            }
            exit();
    }
}

// Obtener horarios con información relacionada
$stmt = $pdo->prepare("
    SELECT h.id, h.dia_semana, h.hora_inicio, h.hora_fin,
           c.nombre as curso_nombre, c.codigo as curso_codigo,
           s.nombre as salon_nombre,
           CONCAT(u.nombre, ' ', u.apellido) as teacher_nombre,
           h.curso_id, h.salon_id, h.teacher_id
    FROM horarios h
    LEFT JOIN cursos c ON h.curso_id = c.id
    LEFT JOIN salones s ON h.salon_id = s.id
    LEFT JOIN usuarios u ON h.teacher_id = u.id
    ORDER BY h.dia_semana, h.hora_inicio
");
$stmt->execute();
$horarios = $stmt->fetchAll();

// Obtener cursos activos
$stmt = $pdo->prepare("SELECT id, nombre, codigo FROM cursos WHERE estado IN ('en_inscripcion', 'activo') ORDER BY nombre");
$stmt->execute();
$cursos = $stmt->fetchAll();

// Obtener salones activos
$stmt = $pdo->prepare("SELECT id, nombre FROM salones WHERE estado = 'activo' ORDER BY nombre");
$stmt->execute();
$salones = $stmt->fetchAll();

// Obtener profesores (usuarios con rol de profesor)
$stmt = $pdo->prepare("SELECT id, nombre, apellido FROM usuarios WHERE rol_id = 3 AND estado = 'activo' ORDER BY nombre, apellido");
$stmt->execute();
$profesores = $stmt->fetchAll();

$dias_semana = [
    1 => 'Lunes',
    2 => 'Martes', 
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
    7 => 'Domingo'
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Horarios - Our Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Favicon -->
    <link rel="icon" href="/ourcenter/images/favicon/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" sizes="180x180" href="/ourcenter/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/ourcenter/images/favicon/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/ourcenter/images/favicon/android-chrome-512x512.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/ourcenter/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/ourcenter/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="/ourcenter/images/favicon/site.webmanifest">
    <meta name="theme-color" content="#0a1b5c">
    <link rel="stylesheet" href="/ourcenter/css/dashboard.css">

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
        @media (max-width: 768px) {
    .page-content {
        padding: 10px;
    }
}

        .card-header {
            background-color: var(--light-bg);
            border-bottom: 1px solid var(--border-color);
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

        .horario-card {
            transition: transform 0.2s;
        }

        .horario-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .dia-badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }

        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--info-color));
            color: white;
        }

        .form-floating > label {
            color: var(--text-muted);
        }

        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            margin: 0 0.125rem;
        }
        .navbar-nav .dropdown-menu{
            position: absolute !important;
        }
    </style>
</head>
<body>
    <?php include '../preloader.php';?>
    <?php include '../header.php'; ?>

    <!-- Sidebar Toggle Button -->
    <div class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </div>

    <div class="container-fluid">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-calendar-alt me-2"></i>Gestión de Horarios
                </h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalHorario">
                    <i class="fas fa-plus me-1"></i>Nuevo Horario
                </button>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Horarios
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($horarios) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                        Cursos Activos
                                    </div>
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
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Salones Disponibles
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($salones) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-door-open fa-2x text-gray-300"></i>
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
                                        Profesores Activos
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($profesores) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vista de Calendario Semanal -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-week me-2"></i>Vista Semanal de Horarios
                    </h6>
                </div>
                <div class="card-body">
                    <div class="schedule-grid">
                        <?php foreach ($dias_semana as $dia_num => $dia_nombre): ?>
                            <div class="card horario-card">
                                <div class="card-header bg-primary text-white text-center">
                                    <strong><?= $dia_nombre ?></strong>
                                </div>
                                <div class="card-body p-2">
                                    <?php 
                                    $horarios_dia = array_filter($horarios, function($h) use ($dia_num) {
                                        return $h['dia_semana'] == $dia_num;
                                    });
                                    if (empty($horarios_dia)): ?>
                                        <p class="text-muted text-center mb-0"><small>Sin horarios</small></p>
                                    <?php else: ?>
                                        <?php foreach ($horarios_dia as $horario): ?>
                                            <div class="border rounded p-2 mb-2 bg-light">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong class="text-primary"><?= htmlspecialchars($horario['curso_nombre']) ?></strong>
                                                        <br><small class="text-muted"><?= $horario['hora_inicio'] ?> - <?= $horario['hora_fin'] ?></small>
                                                        <br><small><i class="fas fa-door-open me-1"></i><?= htmlspecialchars($horario['salon_nombre']) ?></small>
                                                        <br><small><i class="fas fa-user me-1"></i><?= htmlspecialchars($horario['teacher_nombre']) ?></small>
                                                    </div>
                                                    <div>
                                                        <button class="btn btn-sm btn-outline-primary btn-action" onclick="editarHorario(<?= $horario['id'] ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger btn-action" onclick="eliminarHorario(<?= $horario['id'] ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Tabla de horarios -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-table me-2"></i>Lista Completa de Horarios
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tabla-horarios">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Curso</th>
                                    <th>Día</th>
                                    <th>Hora Inicio</th>
                                    <th>Hora Fin</th>
                                    <th>Salón</th>
                                    <th>Profesor</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($horarios as $horario): ?>
                                    <tr>
                                        <td><?= $horario['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($horario['curso_nombre']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($horario['curso_codigo']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary dia-badge">
                                                <?= $dias_semana[$horario['dia_semana']] ?>
                                            </span>
                                        </td>
                                        <td><?= $horario['hora_inicio'] ?></td>
                                        <td><?= $horario['hora_fin'] ?></td>
                                        <td><?= htmlspecialchars($horario['salon_nombre']) ?></td>
                                        <td><?= htmlspecialchars($horario['teacher_nombre']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editarHorario(<?= $horario['id'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarHorario(<?= $horario['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
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

    <!-- Modal para crear/editar horario -->
    <div class="modal fade" id="modalHorario" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-plus me-2"></i>
                        <span id="modal-title">Nuevo Horario</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formHorario">
                    <div class="modal-body">
                        <input type="hidden" id="horario_id" name="horario_id">
                        <input type="hidden" id="action" name="action" value="crear_horario">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <select class="form-select" id="curso_id" name="curso_id" required>
                                        <option value="">Seleccionar curso</option>
                                        <?php foreach ($cursos as $curso): ?>
                                            <option value="<?= $curso['id'] ?>"><?= htmlspecialchars($curso['nombre']) ?> (<?= $curso['codigo'] ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="curso_id">Curso</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <select class="form-select" id="dia_semana" name="dia_semana" required>
                                        <option value="">Seleccionar día</option>
                                        <?php foreach ($dias_semana as $num => $nombre): ?>
                                            <option value="<?= $num ?>"><?= $nombre ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="dia_semana">Día de la semana</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" required>
                                    <label for="hora_inicio">Hora de inicio</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="time" class="form-control" id="hora_fin" name="hora_fin" required>
                                    <label for="hora_fin">Hora de fin</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <select class="form-select" id="salon_id" name="salon_id" required>
                                        <option value="">Seleccionar salón</option>
                                        <?php foreach ($salones as $salon): ?>
                                            <option value="<?= $salon['id'] ?>"><?= htmlspecialchars($salon['nombre']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="salon_id">Salón</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <select class="form-select" id="teacher_id" name="teacher_id" required>
                                        <option value="">Seleccionar profesor</option>
                                        <?php foreach ($profesores as $profesor): ?>
                                            <option value="<?= $profesor['id'] ?>"><?= htmlspecialchars($profesor['nombre'] . ' ' . $profesor['apellido']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="teacher_id">Profesor</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Botón flotante para agregar -->
    <button class="btn btn-primary btn-floating" data-bs-toggle="modal" data-bs-target="#modalHorario">
        <i class="fas fa-plus"></i>
    </button>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
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
            $('#tabla-horarios').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                "pageLength": 10,
                "order": [[2, "asc"], [3, "asc"]]
            });

            // Limpiar formulario al abrir modal
            $('#modalHorario').on('show.bs.modal', function (e) {
                if (!$(e.relatedTarget).hasClass('edit-btn')) {
                    limpiarFormulario();
                }
            });

            // Manejar envío del formulario
            $('#formHorario').on('submit', function(e) {
                e.preventDefault();
                
                // Validar horarios
                const horaInicio = $('#hora_inicio').val();
                const horaFin = $('#hora_fin').val();
                
                if (horaInicio >= horaFin) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        text: 'La hora de inicio debe ser menor que la hora de fin'
                    });
                    return;
                }

                $.ajax({
                    url: '',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al procesar la solicitud'
                        });
                    }
                });
            });
        });

        function limpiarFormulario() {
            $('#formHorario')[0].reset();
            $('#horario_id').val('');
            $('#action').val('crear_horario');
            $('#modal-title').text('Nuevo Horario');
        }

        function editarHorario(id) {
            // Encontrar el horario en los datos
            const horarios = <?= json_encode($horarios) ?>;
            const horario = horarios.find(h => h.id == id);
            
            if (horario) {
                // Llenar el formulario
                $('#horario_id').val(horario.id);
                $('#curso_id').val(horario.curso_id);
                $('#dia_semana').val(horario.dia_semana);
                $('#hora_inicio').val(horario.hora_inicio);
                $('#hora_fin').val(horario.hora_fin);
                $('#salon_id').val(horario.salon_id);
                $('#teacher_id').val(horario.teacher_id);
                $('#action').val('actualizar_horario');
                $('#modal-title').text('Editar Horario');
                
                // Mostrar modal
                $('#modalHorario').modal('show');
            }
        }

        function eliminarHorario(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '',
                        method: 'POST',
                        data: {
                            action: 'eliminar_horario',
                            horario_id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Eliminado',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrió un error al procesar la solicitud'
                            });
                        }
                    });
                }
            });
        }

        // Validación adicional para evitar conflictos de horarios
        function validarConflictoHorarios() {
            const diaSeleccionado = $('#dia_semana').val();
            const horaInicio = $('#hora_inicio').val();
            const horaFin = $('#hora_fin').val();
            const salonId = $('#salon_id').val();
            const teacherId = $('#teacher_id').val();
            const horarioId = $('#horario_id').val();

            if (!diaSeleccionado || !horaInicio || !horaFin || !salonId || !teacherId) {
                return true; // Si faltan datos, dejar que la validación del formulario maneje esto
            }

            const horarios = <?= json_encode($horarios) ?>;
            
            // Verificar conflictos de salón
            const conflictoSalon = horarios.some(h => 
                h.id != horarioId &&
                h.dia_semana == diaSeleccionado &&
                h.salon_id == salonId &&
                hayConflictoHorario(horaInicio, horaFin, h.hora_inicio, h.hora_fin)
            );

            if (conflictoSalon) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Conflicto de salón',
                    text: 'El salón ya está ocupado en este horario'
                });
                return false;
            }

            // Verificar conflictos de profesor
            const conflictoProfesor = horarios.some(h => 
                h.id != horarioId &&
                h.dia_semana == diaSeleccionado &&
                h.teacher_id == teacherId &&
                hayConflictoHorario(horaInicio, horaFin, h.hora_inicio, h.hora_fin)
            );

            if (conflictoProfesor) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Conflicto de profesor',
                    text: 'El profesor ya tiene clase asignada en este horario'
                });
                return false;
            }

            return true;
        }

        // Función auxiliar para verificar conflicto de horarios
        function hayConflictoHorario(inicio1, fin1, inicio2, fin2) {
            // Convertir a minutos para facilitar comparación
            const toMinutes = (time) => {
                const [hours, minutes] = time.split(':').map(Number);
                return hours * 60 + minutes;
            };

            const inicio1Min = toMinutes(inicio1);
            const fin1Min = toMinutes(fin1);
            const inicio2Min = toMinutes(inicio2);
            const fin2Min = toMinutos(fin2);

            // Verificar solapamiento
            return (inicio1Min < fin2Min && fin1Min > inicio2Min);
        }

        // Agregar validación antes del envío del formulario
        $('#formHorario').on('submit', function(e) {
            e.preventDefault();
            
            // Validar horarios básicos
            const horaInicio = $('#hora_inicio').val();
            const horaFin = $('#hora_fin').val();
            
            if (horaInicio >= horaFin) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    text: 'La hora de inicio debe ser menor que la hora de fin'
                });
                return;
            }

            // Validar conflictos
            if (!validarConflictoHorarios()) {
                return;
            }

            // Proceder con el envío
            $.ajax({
                url: '',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al procesar la solicitud'
                    });
                }
            });
        });

        // Toggle de sidebar
        $('#sidebarToggle').on('click', function() {
            $('body').toggleClass('sidebar-toggled');
            $('.sidebar').toggleClass('toggled');
        });

        // Función para exportar horarios
        function exportarHorarios() {
            Swal.fire({
                title: 'Exportar Horarios',
                text: '¿En qué formato deseas exportar?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'PDF',
                cancelButtonText: 'Excel',
                showDenyButton: true,
                denyButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Exportar PDF
                    window.open('exportar_horarios.php?formato=pdf', '_blank');
                } else if (result.isDismissed && result.dismiss !== 'deny') {
                    // Exportar Excel
                    window.open('exportar_horarios.php?formato=excel', '_blank');
                }
            });
        }

        // Función para imprimir horarios
        function imprimirHorarios() {
            const contenido = document.querySelector('.schedule-grid').innerHTML;
            const ventana = window.open('', '_blank');
            ventana.document.write(`
                <html>
                <head>
                    <title>Horarios - Our Center</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .schedule-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem; }
                        @media print { .btn { display: none; } }
                    </style>
                </head>
                <body>
                    <div class="container-fluid">
                        <h2 class="text-center mb-4">Horarios de Clases - Our Center</h2>
                        <div class="schedule-grid">${contenido}</div>
                    </div>
                </body>
                </html>
            `);
            ventana.document.close();
            ventana.print();
        }

        // Agregar botones de acción adicionales
        $(document).ready(function() {
            const botonesAccion = `
                <div class="btn-group me-2" role="group">
                    <button type="button" class="btn btn-outline-secondary" onclick="imprimirHorarios()">
                        <i class="fas fa-print me-1"></i>Imprimir
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="exportarHorarios()">
                        <i class="fas fa-download me-1"></i>Exportar
                    </button>
                </div>
            `;
            
            $('.d-flex.justify-content-between.align-items-center.mb-4').find('button').before(botonesAccion);
        });

        // Función para filtrar horarios por día
        function filtrarPorDia(dia) {
            if (dia === 'todos') {
                $('.horario-card').show();
            } else {
                $('.horario-card').hide();
                $(`.horario-card:nth-child(${dia})`).show();
            }
        }

        // Agregar filtros rápidos
        $(document).ready(function() {
            const filtros = `
                <div class="mb-3">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary active" onclick="filtrarPorDia('todos')">Todos</button>
                        <button type="button" class="btn btn-outline-primary" onclick="filtrarPorDia(1)">Lunes</button>
                        <button type="button" class="btn btn-outline-primary" onclick="filtrarPorDia(2)">Martes</button>
                        <button type="button" class="btn btn-outline-primary" onclick="filtrarPorDia(3)">Miércoles</button>
                        <button type="button" class="btn btn-outline-primary" onclick="filtrarPorDia(4)">Jueves</button>
                        <button type="button" class="btn btn-outline-primary" onclick="filtrarPorDia(5)">Viernes</button>
                        <button type="button" class="btn btn-outline-primary" onclick="filtrarPorDia(6)">Sábado</button>
                        <button type="button" class="btn btn-outline-primary" onclick="filtrarPorDia(7)">Domingo</button>
                    </div>
                </div>
            `;
            
            $('.schedule-grid').before(filtros);
            
            // Manejar clicks en filtros
            $('.btn-group .btn-outline-primary').on('click', function() {
                $('.btn-group .btn-outline-primary').removeClass('active');
                $(this).addClass('active');
            });
        });
    </script>

    <!-- Script adicional para preloader -->
    <script>
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.style.display = 'none';
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>