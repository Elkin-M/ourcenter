<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');

// pagos.php
require_once '../../config/db.php';

// Obtener lista de pagos
$estadoFiltro = isset($_GET['estado']) ? $_GET['estado'] : '';
$sql = "
SELECT 
    pagos.id,
    usuarios.nombre AS estudiante,
    pagos.monto,
    pagos.fecha_creacion,
    pagos.estado
FROM pagos
JOIN inscripciones ON pagos.inscripcion_id = inscripciones.id
JOIN usuarios ON inscripciones.usuario_id = usuarios.id
";

if ($estadoFiltro) {
    $sql .= " WHERE pagos.estado = :estado";
}

$stmt = $pdo->prepare($sql);

if ($estadoFiltro) {
    $stmt->bindParam(':estado', $estadoFiltro);
}

$stmt->execute();
$pagos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos - Our Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/mstile-144x144.png">
    <link rel="stylesheet" href="../../css/dashboard.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    
    <link rel="stylesheet" href="../../css/dashboard.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

</head>
<body>
    <?php include '../preloader.php'; ?>
    <?php include '../header.php'; ?>
    
    <!-- Sidebar Toggle Button -->
    <div class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </div>

    

    <div class="container mt-5">
        <h1>Lista de Pagos</h1>
        <div class="mb-3">
            <label for="filtroEstado" class="form-label">Filtrar por Estado:</label>
            <select id="filtroEstado" class="form-select" onchange="filtrarPagos()">
                <option value="">Todos</option>
                <option value="completado" <?= $estadoFiltro == 'completado' ? 'selected' : '' ?>>Completado</option>
                <option value="pendiente" <?= $estadoFiltro == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                <option value="fallido" <?= $estadoFiltro == 'fallido' ? 'selected' : '' ?>>Fallido</option>
            </select>
        </div>

        <table id="tablaPagos" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Estudiante</th>
                    <th>Monto</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pagos as $pago): ?>
                    <tr>
                        <td><?= $pago['id'] ?></td>
                        <td><?= $pago['estudiante'] ?></td>
                        <td><?= number_format($pago['monto'], 2) ?> USD</td>
                        <td><?= date('d-m-Y', strtotime($pago['fecha_creacion'])) ?></td>
                        <td>
                            <span class="badge bg-<?= ($pago['estado'] == 'completado') ? 'success' : (($pago['estado'] == 'pendiente') ? 'warning' : 'danger') ?>">
                                <?= ucfirst($pago['estado']) ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="cambiarEstado(<?= $pago['id'] ?>, 'completado')">Marcar como Completado</button>
                            <button class="btn btn-sm btn-danger" onclick="cambiarEstado(<?= $pago['id'] ?>, 'fallido')">Marcar como Fallido</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
                <?php include '../footer.php'; ?>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#tablaPagos').DataTable();
        });

        function filtrarPagos() {
            const estado = document.getElementById('filtroEstado').value;
            window.location.href = `pagos.php?estado=${estado}`;
        }

        function cambiarEstado(id, nuevoEstado) {
            Swal.fire({
                title: 'Confirmar cambio de estado',
                text: `¿Estás seguro de cambiar el estado de este pago a ${nuevoEstado}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Realizar el cambio de estado en la base de datos
                    $.ajax({
                        url: 'cambiar_estado_pago.php',
                        type: 'POST',
                        data: { id: id, estado: nuevoEstado },
                        success: function (response) {
                            if (response == 'success') {
                                Swal.fire('Estado actualizado', '', 'success');
                                location.reload();  // Recargar la página para ver los cambios
                            } else {
                                Swal.fire('Error', 'No se pudo actualizar el estado', 'error');
                            }
                        }
                    });
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
