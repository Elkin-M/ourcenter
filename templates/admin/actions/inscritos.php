<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');
require_once '../../../config/db.php';

// Verificar si se proporcionó un ID de curso
if (!isset($_GET['curso_id'])) {
    header('Location: cursos.php');
    exit();
}

$curso_id = $_GET['curso_id'];

// Obtener información del curso
$stmt = $pdo->prepare("SELECT id, nombre FROM cursos WHERE id = ?");
$stmt->execute([$curso_id]);
$curso = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$curso) {
    header('Location: cursos.php');
    exit();
}

// Obtener lista de estudiantes inscritos en el curso
$stmt = $pdo->prepare("
    SELECT u.id, u.nombre, u.apellido, u.email, u.telefono, i.fecha_creacion
    FROM usuarios u
    JOIN inscripciones i ON u.id = i.usuario_id
    WHERE i.curso_id = ?
");
$stmt->execute([$curso_id]);
$inscritos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estudiantes Inscritos - <?= htmlspecialchars($curso['nombre']) ?> - Our Center</title>
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
    </style>
</head>
<body>
    <?php include '../../preloader.php';?>
    <?php include '../../header.php'; ?>

    <!-- Sidebar Toggle Button -->
    <div class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </div>

    <div class="container-fluid">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-users me-2"></i>Estudiantes Inscritos en <?= htmlspecialchars($curso['nombre']) ?>
                </h1>
                <a href="../cursos.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver a Cursos
                </a>
            </div>

            <!-- Tabla de estudiantes inscritos -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-table me-2"></i>Lista de Estudiantes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tabla-inscritos">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Apellido</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Fecha de Inscripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inscritos as $inscrito): ?>
                                    <tr>
                                        <td><?= $inscrito['id'] ?></td>
                                        <td><?= htmlspecialchars($inscrito['nombre']) ?></td>
                                        <td><?= htmlspecialchars($inscrito['apellido']) ?></td>
                                        <td><?= htmlspecialchars($inscrito['email']) ?></td>
                                        <td><?= htmlspecialchars($inscrito['telefono']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($inscrito['fecha_creacion'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
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
            $('#tabla-inscritos').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                "pageLength": 10,
                "order": [[0, "desc"]]
            });
        });
    </script>
</body>
</html>
