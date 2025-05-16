<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');
require_once '../../config/conexion-courses.php';
// require_once 'includes/functions.php';

// Verificar si el usuario está logueado y es estudiante
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 2) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$current_page = basename($_SERVER['PHP_SELF']);
$error = '';
$success = '';

// Procesar acción específica si existe
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Manejar pago completado
if (isset($_POST['completar_pago'])) {
    $pago_id = $_POST['pago_id'];
    $metodo_pago = $_POST['metodo_pago'];
    $referencia = $_POST['referencia'];
    
    // Subir comprobante si existe
    $comprobante_url = null;
    if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === 0) {
        $upload_dir = 'uploads/comprobantes/';
        $filename = time() . '_' . basename($_FILES['comprobante']['name']);
        $target_file = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $target_file)) {
            $comprobante_url = $target_file;
        } else {
            $error = "Error al subir el comprobante.";
        }
    }
    
    if (empty($error)) {
        // Actualizar el pago
        $query = "UPDATE pagos SET metodo_pago = ?, referencia_externa = ?, 
                  estado = 'completado', comprobante_url = ? 
                  WHERE id = ? AND estado = 'pendiente'";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("sssi", $metodo_pago, $referencia, $comprobante_url, $pago_id);
        
        if ($stmt->execute()) {
            // Registrar la actividad
            registrarLog($conexion, $usuario_id, "Pago completado", "pagos", $pago_id, 
                        "Método: $metodo_pago, Referencia: $referencia");
            
            $success = "Pago registrado correctamente. Será validado por un administrador.";
        } else {
            $error = "Error al procesar el pago: " . $conexion->error;
        }
    }
}

// Función para obtener pagos del usuario
function obtenerPagosUsuario($conexion, $usuario_id, $estado = null) {
    $query = "SELECT p.*, i.id as inscripcion_id, c.nombre as curso_nombre, c.codigo as curso_codigo 
              FROM pagos p 
              JOIN inscripciones i ON p.inscripcion_id = i.id 
              JOIN cursos c ON i.curso_id = c.id 
              WHERE i.usuario_id = ?";
    
    if ($estado) {
        $query .= " AND p.estado = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("is", $usuario_id, $estado);
    } else {
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $usuario_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Obtener pagos según la acción solicitada
if ($action == 'pendientes') {
    $pagos = obtenerPagosUsuario($conexion, $usuario_id, 'pendiente');
    $titulo_pagina = "Pagos Pendientes";
} else {
    $pagos = obtenerPagosUsuario($conexion, $usuario_id);
    $titulo_pagina = "Mis Pagos";
}

// Obtener detalles de un pago específico
$pago_detalle = null;
if (isset($_GET['id'])) {
    $pago_id = $_GET['id'];
    $query = "SELECT p.*, i.id as inscripcion_id, c.nombre as curso_nombre, c.codigo as curso_codigo, 
              c.precio, u.nombre as nombre_estudiante, u.apellido as apellido_estudiante, 
              u.email as email_estudiante 
              FROM pagos p 
              JOIN inscripciones i ON p.inscripcion_id = i.id 
              JOIN cursos c ON i.curso_id = c.id 
              JOIN usuarios u ON i.usuario_id = u.id 
              WHERE p.id = ? AND i.usuario_id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ii", $pago_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pago_detalle = $result->fetch_assoc();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina; ?> - OurCenter</title>
    <?php include 'includes/estudiante-sidebar.php'; ?>
    <?php include 'includes/estudiante-header.php'; ?>
    <style>
        .payment-card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .payment-header {
            border-bottom: 1px solid #eee;
            padding: 15px;
        }
        .payment-body {
            padding: 20px;
        }
        .payment-actions {
            background-color: #f8f9fa;
            padding: 15px;
            border-top: 1px solid #eee;
        }
        .badge-pendiente {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-completado {
            background-color: #28a745;
            color: #fff;
        }
        .badge-fallido {
            background-color: #dc3545;
            color: #fff;
        }
        .badge-reembolsado {
            background-color: #6c757d;
            color: #fff;
        }
        .comprobante-preview {
            max-width: 100%;
            max-height: 300px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <?php include 'includes/sidebar.php'; ?>
            </div>
            
            <!-- Main content -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo $titulo_pagina; ?></li>
                    </ol>
                </nav>
                
                <h1 class="h2 mb-4"><?php echo $titulo_pagina; ?></h1>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($pago_detalle)): ?>
                    <!-- Detalle de pago -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Detalle del Pago #<?php echo $pago_detalle['id']; ?></h5>
                            <a href="pagos.php" class="btn btn-sm btn-outline-secondary">Volver</a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted">Información del Curso</h6>
                                    <p><strong>Curso:</strong> <?php echo $pago_detalle['curso_nombre']; ?> (<?php echo $pago_detalle['curso_codigo']; ?>)</p>
                                    <p><strong>Monto:</strong> $<?php echo number_format($pago_detalle['monto'], 2); ?></p>
                                    <p><strong>Estado:</strong> 
                                        <span class="badge badge-<?php echo $pago_detalle['estado']; ?>">
                                            <?php echo ucfirst($pago_detalle['estado']); ?>
                                        </span>
                                    </p>
                                    <p><strong>Fecha de Vencimiento:</strong> 
                                        <?php echo !empty($pago_detalle['fecha_vencimiento']) ? date('d/m/Y', strtotime($pago_detalle['fecha_vencimiento'])) : 'No especificada'; ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Detalles del Pago</h6>
                                    <?php if ($pago_detalle['estado'] == 'completado' || $pago_detalle['estado'] == 'reembolsado'): ?>
                                        <p><strong>Método de Pago:</strong> <?php echo ucfirst($pago_detalle['metodo_pago']); ?></p>
                                        <p><strong>Referencia:</strong> <?php echo !empty($pago_detalle['referencia_externa']) ? $pago_detalle['referencia_externa'] : 'No disponible'; ?></p>
                                        <p><strong>Fecha de Pago:</strong> <?php echo date('d/m/Y H:i', strtotime($pago_detalle['fecha_actualizacion'])); ?></p>
                                        
                                        <?php if (!empty($pago_detalle['comprobante_url'])): ?>
                                            <p><strong>Comprobante:</strong></p>
                                            <img src="<?php echo $pago_detalle['comprobante_url']; ?>" class="comprobante-preview" alt="Comprobante de pago">
                                            <a href="<?php echo $pago_detalle['comprobante_url']; ?>" class="btn btn-sm btn-outline-primary mt-2" target="_blank">
                                                <i class="fas fa-download"></i> Descargar Comprobante
                                            </a>
                                        <?php endif; ?>
                                        
                                    <?php elseif ($pago_detalle['estado'] == 'pendiente'): ?>
                                        <div class="alert alert-warning">
                                            <p class="mb-0">Este pago está pendiente. Por favor, complete el proceso de pago.</p>
                                        </div>
                                        
                                        <form method="post" action="pagos.php?id=<?php echo $pago_detalle['id']; ?>" enctype="multipart/form-data">
                                            <input type="hidden" name="pago_id" value="<?php echo $pago_detalle['id']; ?>">
                                            
                                            <div class="mb-3">
                                                <label for="metodo_pago" class="form-label">Método de Pago</label>
                                                <select name="metodo_pago" id="metodo_pago" class="form-select" required>
                                                    <option value="">Seleccione un método</option>
                                                    <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                                                    <option value="transferencia">Transferencia Bancaria</option>
                                                    <option value="efectivo">Efectivo</option>
                                                    <option value="otro">Otro</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="referencia" class="form-label">Número de Referencia</label>
                                                <input type="text" class="form-control" id="referencia" name="referencia" placeholder="Número de transacción, últimos 4 dígitos, etc.">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="comprobante" class="form-label">Comprobante de Pago (opcional)</label>
                                                <input type="file" class="form-control" id="comprobante" name="comprobante" accept="image/*,.pdf">
                                                <div class="form-text">Suba una imagen o PDF de su comprobante de pago (máx. 2MB)</div>
                                            </div>
                                            
                                            <button type="submit" name="completar_pago" class="btn btn-primary">
                                                <i class="fas fa-check-circle"></i> Confirmar Pago
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <div class="alert alert-danger">
                                            <p class="mb-0">Este pago ha sido marcado como <?php echo $pago_detalle['estado']; ?>. Contacte a soporte para más información.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Filtros y opciones -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="btn-group" role="group">
                                <a href="pagos.php" class="btn <?php echo empty($action) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    Todos
                                </a>
                                <a href="pagos.php?action=pendientes" class="btn <?php echo $action == 'pendientes' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    Pendientes
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="inscripciones.php?action=nueva" class="btn btn-success">
                                <i class="fas fa-plus-circle"></i> Nueva Inscripción
                            </a>
                        </div>
                    </div>
                    
                    <!-- Lista de pagos -->
                    <?php if (empty($pagos)): ?>
                        <div class="alert alert-info">
                            <p class="mb-0">
                                <?php 
                                if ($action == 'pendientes') {
                                    echo 'No tienes pagos pendientes.';
                                } else {
                                    echo 'No tienes pagos registrados.';
                                }
                                ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($pagos as $pago): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="payment-card">
                                        <div class="payment-header">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="card-title mb-0"><?php echo $pago['curso_nombre']; ?></h5>
                                                <span class="badge badge-<?php echo $pago['estado']; ?>">
                                                    <?php echo ucfirst($pago['estado']); ?>
                                                </span>
                                            </div>
                                            <small class="text-muted"><?php echo $pago['curso_codigo']; ?></small>
                                        </div>
                                        <div class="payment-body">
                                            <p><strong>Monto:</strong> $<?php echo number_format($pago['monto'], 2); ?></p>
                                            <p><strong>Fecha:</strong> 
                                                <?php echo date('d/m/Y', strtotime($pago['fecha_creacion'])); ?>
                                            </p>
                                            <?php if (!empty($pago['fecha_vencimiento'])): ?>
                                                <p><strong>Vencimiento:</strong> 
                                                    <?php echo date('d/m/Y', strtotime($pago['fecha_vencimiento'])); ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($pago['estado'] == 'completado'): ?>
                                                <p><strong>Método:</strong> <?php echo ucfirst($pago['metodo_pago']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="payment-actions">
                                            <a href="pagos.php?id=<?php echo $pago['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i> Ver Detalles
                                            </a>
                                            <?php if ($pago['estado'] == 'pendiente'): ?>
                                                <a href="pagos.php?id=<?php echo $pago['id']; ?>" class="btn btn-success btn-sm">
                                                    <i class="fas fa-credit-card"></i> Pagar
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/estudiante-footer.php'; ?>
    
    <script>
        // Vista previa de comprobante antes de subir
        document.addEventListener('DOMContentLoaded', function() {
            const comprobanteInput = document.getElementById('comprobante');
            if (comprobanteInput) {
                comprobanteInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file && file.type.match('image.*')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const previewContainer = document.createElement('div');
                            previewContainer.className = 'mt-3';
                            previewContainer.innerHTML = `
                                <p><strong>Vista previa:</strong></p>
                                <img src="${e.target.result}" class="comprobante-preview" alt="Vista previa del comprobante">
                            `;
                            
                            // Eliminar vista previa anterior si existe
                            const existingPreview = comprobanteInput.nextElementSibling.nextElementSibling;
                            if (existingPreview && existingPreview.classList.contains('mt-3')) {
                                existingPreview.remove();
                            }
                            
                            comprobanteInput.nextElementSibling.after(previewContainer);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
</body>
</html>