<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');
require_once '../../config/db.php';

// Obtener lista de todos los profesores con sus salones y cursos asignados
$stmt = $pdo->prepare("
    SELECT u.id, u.nombre, u.apellido, u.email, u.telefono, u.fecha_creacion,
           COUNT(DISTINCT s.id) as salones_asignados,
           COUNT(DISTINCT cs.curso_id) as cursos_asignados,
           GROUP_CONCAT(DISTINCT s.nombre SEPARATOR ', ') as nombres_salones
    FROM usuarios u
    LEFT JOIN salones s ON u.id = s.teacher_id
    LEFT JOIN curso_salones cs ON s.id = cs.salon_id
    WHERE u.rol_id = 3
    GROUP BY u.id
    ORDER BY u.fecha_creacion DESC
");
$stmt->execute();
$profesores = $stmt->fetchAll();

// Obtener estadísticas adicionales
$stmt_stats = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT u.id) as total_profesores,
        COUNT(DISTINCT s.id) as total_salones,
        COUNT(DISTINCT cs.curso_id) as total_cursos_con_salon,
        COUNT(DISTINCT CASE WHEN s.id IS NOT NULL THEN u.id END) as profesores_con_salon
    FROM usuarios u
    LEFT JOIN salones s ON u.id = s.teacher_id
    LEFT JOIN curso_salones cs ON s.id = cs.salon_id
    WHERE u.rol_id = 3
");
$stmt_stats->execute();
$stats = $stmt_stats->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profesores - Our Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Favicon -->
    <link rel="icon" href="../images/favicon/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" sizes="180x180" href="../images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../images/favicon/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="../images/favicon/android-chrome-512x512.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../images/favicon/favicon-16x16.png">
    <link rel="manifest" href="../images/favicon/site.webmanifest">
    <meta name="theme-color" content="#0a1b5c">
    <link rel="stylesheet" href="../css/dashboard.css">

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

        .badge {
            font-size: 0.75rem;
        }

        .stats-card {
            background: linear-gradient(135deg, var(--success-color), #36d59e);
            color: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }

        .action-buttons .btn {
            margin: 0 2px;
            padding: 5px 10px;
            font-size: 0.75rem;
        }

        .professor-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--success-color), #36d59e);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        .salon-badge {
            display: inline-block;
            margin: 2px;
            padding: 3px 8px;
            background-color: var(--info-color);
            color: white;
            border-radius: 12px;
            font-size: 0.7rem;
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
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-chalkboard-teacher me-2"></i>Gestión de Profesores
                </h1>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevoProfesor">
                    <i class="fas fa-plus me-1"></i>Nuevo Profesor
                </button>
            </div>

            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">
                                        Total Profesores
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold">
                                        <?= $stats['total_profesores'] ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chalkboard-teacher fa-2x opacity-75"></i>
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
                                        Salones Asignados
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= $stats['total_salones'] ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-door-open fa-2x text-info"></i>
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
                                        Cursos Conectados
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= $stats['total_cursos_con_salon'] ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-book fa-2x text-warning"></i>
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
                                        Profesores Activos
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= $stats['profesores_con_salon'] ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-check fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de profesores -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-table me-2"></i>Lista de Profesores
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tabla-profesores">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre Completo</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Salones</th>
                                    <th>Cursos</th>
                                    <th>Estado</th>
                                    <th>Fecha Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($profesores as $profesor): ?>
                                    <tr>
                                        <td><?= $profesor['id'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="professor-avatar me-2">
                                                    <?= strtoupper(substr($profesor['nombre'], 0, 1) . substr($profesor['apellido'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <strong><?= htmlspecialchars($profesor['nombre'] . ' ' . $profesor['apellido']) ?></strong>
                                                    <br>
                                                    <small class="text-muted">Profesor</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($profesor['email']) ?></td>
                                        <td><?= htmlspecialchars($profesor['telefono']) ?></td>
                                        <td>
                                            <?php if ($profesor['salones_asignados'] > 0): ?>
                                                <span class="badge bg-info"><?= $profesor['salones_asignados'] ?> salón<?= $profesor['salones_asignados'] > 1 ? 'es' : '' ?></span>
                                                <?php if ($profesor['nombres_salones']): ?>
                                                    <br>
                                                    <small class="text-muted"><?= htmlspecialchars($profesor['nombres_salones']) ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Sin salones</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($profesor['cursos_asignados'] > 0): ?>
                                                <span class="badge bg-success"><?= $profesor['cursos_asignados'] ?> curso<?= $profesor['cursos_asignados'] > 1 ? 's' : '' ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Sin cursos</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($profesor['salones_asignados'] > 0): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Disponible</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($profesor['fecha_creacion'])) ?></td>
                                        <td class="action-buttons">
                                            <button class="btn btn-info btn-sm" onclick="verProfesor(<?= $profesor['id'] ?>)" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-warning btn-sm" onclick="editarProfesor(<?= $profesor['id'] ?>)" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-primary btn-sm" onclick="verSalones(<?= $profesor['id'] ?>)" title="Ver salones">
                                                <i class="fas fa-door-open"></i>
                                            </button>
                                            <?php if ($profesor['cursos_asignados'] > 0): ?>
                                                <button class="btn btn-success btn-sm" onclick="verCursos(<?= $profesor['id'] ?>)" title="Ver cursos">
                                                    <i class="fas fa-chalkboard"></i>
                                                </button>
                                            <?php endif; ?>
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

    <!-- Modal Nuevo Profesor -->
    <div class="modal fade" id="modalNuevoProfesor" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Nuevo Profesor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formNuevoProfesor">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Apellido</label>
                                <input type="text" class="form-control" name="apellido" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Especialidad</label>
                            <select class="form-control" name="especialidad" required>
                                <option value="">Seleccionar especialidad</option>
                                <option value="Inglés Básico">Inglés Básico</option>
                                <option value="Inglés Intermedio">Inglés Intermedio</option>
                                <option value="Inglés Avanzado">Inglés Avanzado</option>
                                <option value="Inglés Conversacional">Inglés Conversacional</option>
                                <option value="Inglés de Negocios">Inglés de Negocios</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Registrar Profesor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            $('#tabla-profesores').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                "pageLength": 10,
                "order": [[0, "desc"]]
            });

            // Formulario nuevo profesor
            $('#formNuevoProfesor').on('submit', function(e) {
                e.preventDefault();
                // Aquí iría la lógica para enviar los datos del formulario
                Swal.fire({
                    title: 'Profesor registrado',
                    text: 'El profesor ha sido registrado exitosamente',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            });
        });

        function verProfesor(id) {
            // Hacer petición AJAX para obtener detalles del profesor
            fetch(`actions/obtener_profesor.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        title: 'Detalles del Profesor',
                        html: `
                            <div class="text-start">
                                <p><strong>Nombre:</strong> ${data.nombre} ${data.apellido}</p>
                                <p><strong>Email:</strong> ${data.email}</p>
                                <p><strong>Teléfono:</strong> ${data.telefono}</p>
                                <p><strong>Salones asignados:</strong> ${data.salones_asignados}</p>
                                <p><strong>Cursos conectados:</strong> ${data.cursos_asignados}</p>
                                <p><strong>Estado:</strong> ${data.estado}</p>
                            </div>
                        `,
                        icon: 'info',
                        confirmButtonText: 'Cerrar'
                    });
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudieron cargar los detalles del profesor',
                        icon: 'error'
                    });
                });
        }

        function editarProfesor(id) {
            Swal.fire({
                title: 'Editar Profesor',
                html: 'Aquí se abriría el formulario de edición para el profesor ID: ' + id,
                icon: 'info',
                confirmButtonText: 'Cerrar'
            });
        }

        function verSalones(id) {
            window.location.href = 'salones.php?profesor_id=' + id;
        }

        function verCursos(id) {
            // Redirigir a la página de cursos filtrada por los salones del profesor
            window.location.href = 'cursos.php?profesor_id=' + id;
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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