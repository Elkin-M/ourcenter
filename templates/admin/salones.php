<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');
require_once '../../config/db.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] !== 1) {
    header('Location: /ourcenter/config/login.php');
    exit();
}

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'crear':
                $nombre = $_POST['nombre'];
                $descripcion = $_POST['descripcion'];
                $teacher_id = $_POST['teacher_id'];
                $capacidad = $_POST['capacidad'];
                $codigo = $_POST['codigo'];
                $nivel = $_POST['nivel'];
                $curso_id = $_POST['curso_id'] ?: null;
                
                $stmt = $pdo->prepare("INSERT INTO salones (nombre, descripcion, teacher_id, capacidad, codigo, nivel, curso_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $descripcion, $teacher_id, $capacidad, $codigo, $nivel, $curso_id]);
                
                $_SESSION['mensaje'] = 'Salón creado exitosamente';
                $_SESSION['tipo_mensaje'] = 'success';
                break;
                
            case 'editar':
                $id = $_POST['id'];
                $nombre = $_POST['nombre'];
                $descripcion = $_POST['descripcion'];
                $teacher_id = $_POST['teacher_id'];
                $capacidad = $_POST['capacidad'];
                $codigo = $_POST['codigo'];
                $nivel = $_POST['nivel'];
                $curso_id = $_POST['curso_id'] ?: null;
                $estado = $_POST['estado'];
                
                $stmt = $pdo->prepare("UPDATE salones SET nombre = ?, descripcion = ?, teacher_id = ?, capacidad = ?, codigo = ?, nivel = ?, curso_id = ?, estado = ? WHERE id = ?");
                $stmt->execute([$nombre, $descripcion, $teacher_id, $capacidad, $codigo, $nivel, $curso_id, $estado, $id]);
                
                $_SESSION['mensaje'] = 'Salón actualizado exitosamente';
                $_SESSION['tipo_mensaje'] = 'success';
                break;
                
            case 'eliminar':
                $id = $_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM salones WHERE id = ?");
                $stmt->execute([$id]);
                
                $_SESSION['mensaje'] = 'Salón eliminado exitosamente';
                $_SESSION['tipo_mensaje'] = 'success';
                break;
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Obtener lista de salones con información relacionada
$stmt = $pdo->prepare("
    SELECT s.*, 
           u.nombre as teacher_nombre, 
           u.apellido as teacher_apellido,
           c.nombre as curso_nombre,
           COUNT(us.id) as total_estudiantes
    FROM salones s
    LEFT JOIN usuarios u ON s.teacher_id = u.id
    LEFT JOIN cursos c ON s.curso_id = c.id
    LEFT JOIN usuarios us ON us.salon_id = s.id
    GROUP BY s.id
    ORDER BY s.fecha_creacion DESC
");
$stmt->execute();
$salones = $stmt->fetchAll();

// Obtener lista de profesores para el select
$stmt = $pdo->prepare("SELECT id, nombre, apellido FROM usuarios WHERE rol_id = 3 AND estado = 'activo'");
$stmt->execute();
$profesores = $stmt->fetchAll();

// Obtener lista de cursos para el select
$stmt = $pdo->prepare("SELECT id, nombre FROM cursos WHERE estado IN ('en_inscripcion', 'activo')");
$stmt->execute();
$cursos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Salones - Our Center</title>
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

        .badge-status {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }

        .badge-activo {
            background-color: var(--success-color);
        }

        .badge-inactivo {
            background-color: var(--danger-color);
        }

        .stats-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, #224abe 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .stats-card .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stats-card .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
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
                    <i class="fas fa-chalkboard-teacher me-2"></i>Gestión de Salones
                </h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSalon">
                    <i class="fas fa-plus me-1"></i>Nuevo Salón
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-number"><?= count($salones) ?></div>
                        <div class="stats-label">
                            <i class="fas fa-chalkboard-teacher me-1"></i>Total Salones
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card" style="background: linear-gradient(135deg, var(--success-color) 0%, #17a673 100%);">
                        <div class="stats-number"><?= count(array_filter($salones, fn($s) => $s['estado'] == 'activo')) ?></div>
                        <div class="stats-label">
                            <i class="fas fa-check-circle me-1"></i>Salones Activos
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card" style="background: linear-gradient(135deg, var(--info-color) 0%, #2c9faf 100%);">
                        <div class="stats-number"><?= array_sum(array_column($salones, 'total_estudiantes')) ?></div>
                        <div class="stats-label">
                            <i class="fas fa-users me-1"></i>Total Estudiantes
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card" style="background: linear-gradient(135deg, var(--warning-color) 0%, #dda20a 100%);">
                        <div class="stats-number"><?= array_sum(array_column($salones, 'capacidad')) ?></div>
                        <div class="stats-label">
                            <i class="fas fa-chair me-1"></i>Capacidad Total
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de salones -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-table me-2"></i>Lista de Salones
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tabla-salones">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Profesor</th>
                                    <th>Curso</th>
                                    <th>Capacidad</th>
                                    <th>Estudiantes</th>
                                    <th>Nivel</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($salones as $salon): ?>
                                    <tr>
                                        <td><?= $salon['id'] ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($salon['codigo']) ?></span></td>
                                        <td><?= htmlspecialchars($salon['nombre']) ?></td>
                                        <td><?= htmlspecialchars($salon['teacher_nombre'] . ' ' . $salon['teacher_apellido']) ?></td>
                                        <td><?= $salon['curso_nombre'] ? htmlspecialchars($salon['curso_nombre']) : '<span class="text-muted">Sin asignar</span>' ?></td>
                                        <td><?= $salon['capacidad'] ?></td>
                                        <td><?= $salon['total_estudiantes'] ?></td>
                                        <td>
                                            <?php if ($salon['nivel']): ?>
                                                <span class="badge bg-info"><?= ucfirst($salon['nivel']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">No definido</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-status <?= $salon['estado'] == 'activo' ? 'badge-activo' : 'badge-inactivo' ?>">
                                                <?= ucfirst($salon['estado']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-info btn-editar" 
                                                        data-id="<?= $salon['id'] ?>"
                                                        data-nombre="<?= htmlspecialchars($salon['nombre']) ?>"
                                                        data-descripcion="<?= htmlspecialchars($salon['descripcion']) ?>"
                                                        data-teacher="<?= $salon['teacher_id'] ?>"
                                                        data-capacidad="<?= $salon['capacidad'] ?>"
                                                        data-codigo="<?= htmlspecialchars($salon['codigo']) ?>"
                                                        data-nivel="<?= $salon['nivel'] ?>"
                                                        data-curso="<?= $salon['curso_id'] ?>"
                                                        data-estado="<?= $salon['estado'] ?>"
                                                        title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-success btn-estudiantes" 
                                                        data-id="<?= $salon['id'] ?>"
                                                        data-nombre="<?= htmlspecialchars($salon['nombre']) ?>"
                                                        title="Ver estudiantes">
                                                    <i class="fas fa-users"></i>
                                                </button>
                                                <button class="btn btn-danger btn-eliminar" 
                                                        data-id="<?= $salon['id'] ?>"
                                                        data-nombre="<?= htmlspecialchars($salon['nombre']) ?>"
                                                        title="Eliminar">
                                                    <i class="fas fa-trash"></i>
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

    <!-- Modal para crear/editar salón -->
    <div class="modal fade" id="modalSalon" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSalonTitle">Nuevo Salón</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formSalon" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="accion" id="accion" value="crear">
                        <input type="hidden" name="id" id="salon_id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre del Salón *</label>
                                    <input type="text" class="form-control" name="nombre" id="nombre" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="codigo" class="form-label">Código del Salón *</label>
                                    <input type="text" class="form-control" name="codigo" id="codigo" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" id="descripcion" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="teacher_id" class="form-label">Profesor Asignado *</label>
                                    <select class="form-select" name="teacher_id" id="teacher_id" required>
                                        <option value="">Seleccionar profesor</option>
                                        <?php foreach ($profesores as $profesor): ?>
                                            <option value="<?= $profesor['id'] ?>">
                                                <?= htmlspecialchars($profesor['nombre'] . ' ' . $profesor['apellido']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="capacidad" class="form-label">Capacidad *</label>
                                    <input type="number" class="form-control" name="capacidad" id="capacidad" min="1" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nivel" class="form-label">Nivel</label>
                                    <select class="form-select" name="nivel" id="nivel">
                                        <option value="">Seleccionar nivel</option>
                                        <option value="básico">Básico</option>
                                        <option value="intermedio">Intermedio</option>
                                        <option value="avanzado">Avanzado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="curso_id" class="form-label">Curso Asociado</label>
                                    <select class="form-select" name="curso_id" id="curso_id">
                                        <option value="">Sin asignar</option>
                                        <?php foreach ($cursos as $curso): ?>
                                            <option value="<?= $curso['id'] ?>">
                                                <?= htmlspecialchars($curso['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" id="estado-group" style="display: none;">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="estado">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar Salón</button>
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
            $('#tabla-salones').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                "pageLength": 10,
                "order": [[0, "desc"]],
                "columnDefs": [
                    { "orderable": false, "targets": 9 }
                ]
            });

            // Mostrar mensajes de sesión
            <?php if (isset($_SESSION['mensaje'])): ?>
                Swal.fire({
                    icon: '<?= $_SESSION['tipo_mensaje'] ?>',
                    title: '<?= $_SESSION['mensaje'] ?>',
                    showConfirmButton: false,
                    timer: 3000
                });
                <?php 
                unset($_SESSION['mensaje']);
                unset($_SESSION['tipo_mensaje']);
                ?>
            <?php endif; ?>

            // Manejar clic en botón editar
            $('.btn-editar').click(function() {
                const data = $(this).data();
                
                $('#modalSalonTitle').text('Editar Salón');
                $('#accion').val('editar');
                $('#salon_id').val(data.id);
                $('#nombre').val(data.nombre);
                $('#descripcion').val(data.descripcion);
                $('#teacher_id').val(data.teacher);
                $('#capacidad').val(data.capacidad);
                $('#codigo').val(data.codigo);
                $('#nivel').val(data.nivel);
                $('#curso_id').val(data.curso);
                $('#estado').val(data.estado);
                $('#estado-group').show();
                $('#btnGuardar').text('Actualizar Salón');
                
                $('#modalSalon').modal('show');
            });

            // Manejar clic en botón eliminar
            $('.btn-eliminar').click(function() {
                const id = $(this).data('id');
                const nombre = $(this).data('nombre');
                
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `¿Deseas eliminar el salón "${nombre}"? Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = $('<form method="POST">' +
                            '<input type="hidden" name="accion" value="eliminar">' +
                            '<input type="hidden" name="id" value="' + id + '">' +
                            '</form>');
                        $('body').append(form);
                        form.submit();
                    }
                });
            });

            // Manejar clic en botón estudiantes
            $('.btn-estudiantes').click(function() {
                const id = $(this).data('id');
                const nombre = $(this).data('nombre');
                
                // Aquí puedes implementar la lógica para mostrar los estudiantes del salón
                // Por ejemplo, redirigir a una página de estudiantes por salón
                window.location.href = `estudiantes_salon.php?salon_id=${id}`;
            });

            // Resetear modal al cerrarlo
            $('#modalSalon').on('hidden.bs.modal', function() {
                $('#formSalon')[0].reset();
                $('#modalSalonTitle').text('Nuevo Salón');
                $('#accion').val('crear');
                $('#salon_id').val('');
                $('#estado-group').hide();
                $('#btnGuardar').text('Guardar Salón');
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