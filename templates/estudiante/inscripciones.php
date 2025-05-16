<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');
require_once '../../config/conexion-courses.php';
require_once '../../server/config.php';
// require_once 'includes/functions.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$current_page = 'Inscripciones';

// Procesar nueva inscripción si se recibe el formulario
if (isset($_POST['inscribir_curso'])) {
    $curso_id = $_POST['curso_id'];
    
    // Verificar si ya está inscrito
    $stmt = $conn->prepare("SELECT id FROM inscripciones WHERE usuario_id = ? AND curso_id = ?");
    $stmt->bind_param("ii", $usuario_id, $curso_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $mensaje = "Ya estás inscrito en este curso.";
        $tipo_mensaje = "warning";
    } else {
        // Verificar disponibilidad de cupo
        $stmt = $conn->prepare("SELECT cupo_maximo, (SELECT COUNT(*) FROM inscripciones WHERE curso_id = cursos.id) as inscritos FROM cursos WHERE id = ?");
        $stmt->bind_param("i", $curso_id);
        $stmt->execute();
        $curso = $stmt->get_result()->fetch_assoc();
        
        if ($curso['inscritos'] >= $curso['cupo_maximo']) {
            $mensaje = "Lo sentimos, no hay cupo disponible en este curso.";
            $tipo_mensaje = "danger";
        } else {
            // Realizar inscripción
            $stmt = $conn->prepare("INSERT INTO inscripciones (usuario_id, curso_id, metodo_inscripcion) VALUES (?, ?, 'online')");
            $stmt->bind_param("ii", $usuario_id, $curso_id);
            
            if ($stmt->execute()) {
                $inscripcion_id = $stmt->insert_id;
                
                // Registrar en log de actividad
                $accion = "Nueva inscripción";
                $tabla = "inscripciones";
                $ip = $_SERVER['REMOTE_ADDR'];
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                
                $stmt = $conn->prepare("INSERT INTO logs_actividad (usuario_id, accion, tabla_afectada, registro_afectado_id, ip_usuario, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ississ", $usuario_id, $accion, $tabla, $inscripcion_id, $ip, $user_agent);
                $stmt->execute();
                
                $mensaje = "Inscripción realizada con éxito. Ahora puedes proceder al pago.";
                $tipo_mensaje = "success";
                
                // Redirigir a la página de pagos
                header("Location: pagos.php?inscripcion_id=$inscripcion_id");
                exit;
            } else {
                $mensaje = "Error al procesar la inscripción. Inténtalo nuevamente.";
                $tipo_mensaje = "danger";
            }
        }
    }
}

// Cancelar inscripción
if (isset($_GET['action']) && $_GET['action'] == 'cancelar' && isset($_GET['id'])) {
    $inscripcion_id = $_GET['id'];
    
    // Verificar que la inscripción pertenece al usuario
    $stmt = $conn->prepare("SELECT id FROM inscripciones WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $inscripcion_id, $usuario_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        // Verificar si hay pagos realizados
        $stmt = $conn->prepare("SELECT id FROM pagos WHERE inscripcion_id = ? AND estado = 'completado'");
        $stmt->bind_param("i", $inscripcion_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $mensaje = "No se puede cancelar la inscripción porque ya tiene pagos realizados. Contacta a soporte.";
            $tipo_mensaje = "warning";
        } else {
            // Actualizar estado de la inscripción
            $stmt = $conn->prepare("UPDATE inscripciones SET estado = 'cancelada' WHERE id = ?");
            $stmt->bind_param("i", $inscripcion_id);
            
            if ($stmt->execute()) {
                $mensaje = "Inscripción cancelada correctamente.";
                $tipo_mensaje = "success";
                
                // Registrar en log de actividad
                $accion = "Cancelación de inscripción";
                $tabla = "inscripciones";
                $ip = $_SERVER['REMOTE_ADDR'];
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                
                $stmt = $conn->prepare("INSERT INTO logs_actividad (usuario_id, accion, tabla_afectada, registro_afectado_id, ip_usuario, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ississ", $usuario_id, $accion, $tabla, $inscripcion_id, $ip, $user_agent);
                $stmt->execute();
            } else {
                $mensaje = "Error al cancelar la inscripción. Inténtalo nuevamente.";
                $tipo_mensaje = "danger";
            }
        }
    } else {
        $mensaje = "No se encontró la inscripción o no tienes permiso para cancelarla.";
        $tipo_mensaje = "danger";
    }
}

// Determinar la vista a mostrar
$view = 'historial'; // Vista por defecto - historial de inscripciones
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'explorar') {
        $view = 'explorar';
    } elseif ($_GET['action'] == 'nueva' && isset($_GET['curso_id'])) {
        $view = 'proceso';
    } elseif ($_GET['action'] == 'nueva') {
        $view = 'explorar';
    }
}

// Obtener datos según la vista seleccionada
if ($view == 'historial') {
    // Obtener historial de inscripciones del usuario
    $stmt = $conexion->prepare("
        SELECT i.*, c.nombre as curso_nombre, c.codigo, c.fecha_inicio, c.fecha_fin, c.modalidad, c.precio,
        (SELECT COUNT(*) FROM pagos WHERE inscripcion_id = i.id AND estado = 'completado') as pagos_completados
        FROM inscripciones i
        JOIN cursos c ON i.curso_id = c.id
        WHERE i.usuario_id = ?
        ORDER BY i.fecha_creacion DESC
    ");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $inscripciones = $stmt->get_result();
} elseif ($view == 'explorar') {
    // Configurar filtros
    $where_conditions = ["estado = 'en_inscripcion'"]; // Solo cursos en periodo de inscripción
    $params = [];
    $types = "";
    
    if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
        $where_conditions[] = "categoria_id = ?";
        $params[] = $_GET['categoria'];
        $types .= "i";
    }
    
    if (isset($_GET['modalidad']) && !empty($_GET['modalidad'])) {
        $where_conditions[] = "modalidad = ?";
        $params[] = $_GET['modalidad'];
        $types .= "s";
    }
    
    if (isset($_GET['nivel']) && !empty($_GET['nivel'])) {
        $where_conditions[] = "nivel = ?";
        $params[] = $_GET['nivel'];
        $types .= "s";
    }
    
    // Crear consulta dinámica con los filtros
    $where_clause = implode(" AND ", $where_conditions);
    $query = "
        SELECT c.*, cat.nombre as categoria_nombre,
        (SELECT COUNT(*) FROM inscripciones WHERE curso_id = c.id) as total_inscritos
        FROM cursos c
        LEFT JOIN categorias_cursos cat ON c.categoria_id = cat.id
        WHERE $where_clause
        ORDER BY c.fecha_inicio ASC
    ";
    
    $stmt = $conexion->prepare($query);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $cursos_disponibles = $stmt->get_result();
    
    // Obtener las categorías para el filtro
    $categorias = $conexion->query("SELECT id, nombre FROM categorias_cursos ORDER BY nombre");
} elseif ($view == 'proceso') {
    // Obtener información del curso seleccionado
    $curso_id = $_GET['curso_id'];
    $stmt = $conn->prepare("
        SELECT c.*, cat.nombre as categoria_nombre,
        (SELECT COUNT(*) FROM inscripciones WHERE curso_id = c.id) as total_inscritos
        FROM cursos c
        LEFT JOIN categorias_cursos cat ON c.categoria_id = cat.id
        WHERE c.id = ?
    ");
    $stmt->bind_param("i", $curso_id);
    $stmt->execute();
    $curso = $stmt->get_result()->fetch_assoc();
    
    // Obtener información del usuario
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripciones - OurCenter</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php
// Incluir el header y sidebar
include './includes/estudiante-sidebar.php';
include './includes/estudiante-header.php';
?>
    
    <div class="container-fluid">
        <div class="row">            
            <!-- Main content -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Inscripciones</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <?php if ($view != 'explorar'): ?>
                            <a href="inscripciones.php?action=explorar" class="btn btn-sm btn-primary me-2">
                                <i class="fas fa-search me-1"></i> Explorar Cursos
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($view != 'historial'): ?>
                            <a href="inscripciones.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-history me-1"></i> Historial de Inscripciones
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (isset($mensaje)): ?>
                    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                        <?php echo $mensaje; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($view == 'historial'): ?>
                    <!-- Historial de inscripciones -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Mis Inscripciones</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($inscripciones->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Código</th>
                                                <th>Curso</th>
                                                <th>Fecha Inicio</th>
                                                <th>Fecha Fin</th>
                                                <th>Modalidad</th>
                                                <th>Estado</th>
                                                <th>Progreso</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($inscripcion = $inscripciones->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo $inscripcion['codigo']; ?></td>
                                                    <td><?php echo $inscripcion['curso_nombre']; ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($inscripcion['fecha_inicio'])); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($inscripcion['fecha_fin'])); ?></td>
                                                    <td>
                                                        <?php
                                                        switch ($inscripcion['modalidad']) {
                                                            case 'presencial':
                                                                echo '<span class="badge bg-primary">Presencial</span>';
                                                                break;
                                                            case 'virtual':
                                                                echo '<span class="badge bg-success">Virtual</span>';
                                                                break;
                                                            case 'hibrido':
                                                                echo '<span class="badge bg-info">Híbrido</span>';
                                                                break;
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        switch ($inscripcion['estado']) {
                                                            case 'pendiente':
                                                                echo '<span class="badge bg-warning">Pendiente</span>';
                                                                break;
                                                            case 'confirmada':
                                                                echo '<span class="badge bg-success">Confirmada</span>';
                                                                break;
                                                            case 'cancelada':
                                                                echo '<span class="badge bg-danger">Cancelada</span>';
                                                                break;
                                                            case 'completada':
                                                                echo '<span class="badge bg-info">Completada</span>';
                                                                break;
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 10px;">
                                                            <div class="progress-bar progress-bar-striped" role="progressbar" style="width: <?php echo $inscripcion['progreso_porcentaje']; ?>%;" aria-valuenow="<?php echo $inscripcion['progreso_porcentaje']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                        <small class="text-muted"><?php echo $inscripcion['progreso_porcentaje']; ?>%</small>
                                                    </td>
                                                    <td>
                                                        <?php if ($inscripcion['estado'] == 'pendiente'): ?>
                                                            <!-- Si tiene pagos pendientes -->
                                                            <?php if ($inscripcion['pagos_completados'] == 0): ?>
                                                                <a href="pagos.php?inscripcion_id=<?php echo $inscripcion['id']; ?>" class="btn btn-sm btn-success" title="Realizar pago">
                                                                    <i class="fas fa-credit-card"></i>
                                                                </a>
                                                                <a href="inscripciones.php?action=cancelar&id=<?php echo $inscripcion['id']; ?>" class="btn btn-sm btn-danger" title="Cancelar inscripción" onclick="return confirm('¿Estás seguro de cancelar esta inscripción?');">
                                                                    <i class="fas fa-times"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <a href="pagos.php?inscripcion_id=<?php echo $inscripcion['id']; ?>" class="btn btn-sm btn-outline-primary" title="Ver pagos">
                                                                    <i class="fas fa-money-bill"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        <?php elseif ($inscripcion['estado'] == 'confirmada' || $inscripcion['estado'] == 'completada'): ?>
                                                            <a href="mis-cursos.php?curso_id=<?php echo $inscripcion['curso_id']; ?>" class="btn btn-sm btn-primary" title="Ver curso">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="pagos.php?inscripcion_id=<?php echo $inscripcion['id']; ?>" class="btn btn-sm btn-outline-success" title="Ver pagos">
                                                                <i class="fas fa-money-bill"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">No disponible</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <p class="mb-0">No tienes inscripciones registradas. <a href="inscripciones.php?action=explorar">Explora los cursos disponibles</a> para inscribirte.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($view == 'explorar'): ?>
                    <!-- Explorar cursos disponibles -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Filtrar Cursos</h5>
                        </div>
                        <div class="card-body">
                            <form action="inscripciones.php" method="get" class="row g-3">
                                <input type="hidden" name="action" value="explorar">
                                
                                <div class="col-md-3">
                                    <label for="categoria" class="form-label">Categoría</label>
                                    <select class="form-select" id="categoria" name="categoria">
                                        <option value="">Todas las categorías</option>
                                        <?php while ($cat = $categorias->fetch_assoc()): ?>
                                            <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_GET['categoria']) && $_GET['categoria'] == $cat['id']) ? 'selected' : ''; ?>>
                                                <?php echo $cat['nombre']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="modalidad" class="form-label">Modalidad</label>
                                    <select class="form-select" id="modalidad" name="modalidad">
                                        <option value="">Todas las modalidades</option>
                                        <option value="presencial" <?php echo (isset($_GET['modalidad']) && $_GET['modalidad'] == 'presencial') ? 'selected' : ''; ?>>Presencial</option>
                                        <option value="virtual" <?php echo (isset($_GET['modalidad']) && $_GET['modalidad'] == 'virtual') ? 'selected' : ''; ?>>Virtual</option>
                                        <option value="hibrido" <?php echo (isset($_GET['modalidad']) && $_GET['modalidad'] == 'hibrido') ? 'selected' : ''; ?>>Híbrido</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="nivel" class="form-label">Nivel</label>
                                    <select class="form-select" id="nivel" name="nivel">
                                        <option value="">Todos los niveles</option>
                                        <option value="básico" <?php echo (isset($_GET['nivel']) && $_GET['nivel'] == 'básico') ? 'selected' : ''; ?>>Básico</option>
                                        <option value="intermedio" <?php echo (isset($_GET['nivel']) && $_GET['nivel'] == 'intermedio') ? 'selected' : ''; ?>>Intermedio</option>
                                        <option value="avanzado" <?php echo (isset($_GET['nivel']) && $_GET['nivel'] == 'avanzado') ? 'selected' : ''; ?>>Avanzado</option>
                                        <option value="todos" <?php echo (isset($_GET['nivel']) && $_GET['nivel'] == 'todos') ? 'selected' : ''; ?>>Todos los niveles</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-filter me-1"></i> Filtrar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="row row-cols-1 row-cols-md-3 g-4">
                        <?php if ($cursos_disponibles->num_rows > 0): ?>
                            <?php while ($curso = $cursos_disponibles->fetch_assoc()): ?>
                                <div class="col">
                                    <div class="card h-100">
                                        <?php if ($curso['imagen_url']): ?>
                                            <img src="../.<?php echo $curso['imagen_url']; ?>" class="card-img-top" alt="<?php echo $curso['nombre']; ?>">
                                        <?php else: ?>
                                            <div class="card-img-top bg-light text-center py-5">
                                                <i class="fas fa-book fa-4x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo $curso['nombre']; ?></h5>
                                            <h6 class="card-subtitle mb-2 text-muted"><?php echo $curso['categoria_nombre']; ?></h6>
                                            
                                            <p class="card-text mb-1">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar-alt me-1"></i> 
                                                    <?php echo date('d/m/Y', strtotime($curso['fecha_inicio'])); ?> - 
                                                    <?php echo date('d/m/Y', strtotime($curso['fecha_fin'])); ?>
                                                </small>
                                            </p>
                                            
                                            <p class="card-text mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i> 
                                                    <?php echo $curso['duracion_horas']; ?> horas
                                                </small>
                                            </p>
                                            
                                            <?php 
                                            switch ($curso['modalidad']) {
                                                case 'presencial':
                                                    echo '<span class="badge bg-primary mb-2">Presencial</span>';
                                                    break;
                                                case 'virtual':
                                                    echo '<span class="badge bg-success mb-2">Virtual</span>';
                                                    break;
                                                case 'hibrido':
                                                    echo '<span class="badge bg-info mb-2">Híbrido</span>';
                                                    break;
                                            }
                                            ?>
                                            
                                            <?php if ($curso['nivel'] != 'todos'): ?>
                                                <span class="badge bg-secondary mb-2 ms-1"><?php echo ucfirst($curso['nivel']); ?></span>
                                            <?php endif; ?>
                                            
                                            <?php if ($curso['etiqueta']): ?>
                                                <span class="badge bg-warning text-dark mb-2 ms-1"><?php echo $curso['etiqueta']; ?></span>
                                            <?php endif; ?>
                                            
                                            <p class="card-text mb-2"><?php echo substr($curso['descripcion'], 0, 100); ?>...</p>
                                            
                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <span class="h5 mb-0">$<?php echo number_format($curso['precio'], 2); ?></span>
                                                
                                                <?php 
                                                $disponibles = $curso['cupo_maximo'] - $curso['total_inscritos'];
                                                if ($disponibles > 0): 
                                                ?>
                                                    <small class="text-success">
                                                        <i class="fas fa-user-check me-1"></i> 
                                                        <?php echo $disponibles; ?> cupos disponibles
                                                    </small>
                                                <?php else: ?>
                                                    <small class="text-danger">
                                                        <i class="fas fa-ban me-1"></i> 
                                                        Sin cupos disponibles
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="card-footer bg-transparent">
                                            <?php if ($disponibles > 0): ?>
                                                <a href="inscripciones.php?action=nueva&curso_id=<?php echo $curso['id']; ?>" class="btn btn-primary w-100">
                                                    <i class="fas fa-sign-in-alt me-1"></i> Inscribirme
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-secondary w-100" disabled>
                                                    <i class="fas fa-ban me-1"></i> Curso completo
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <p class="mb-0">No se encontraron cursos disponibles con los filtros seleccionados.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($view == 'proceso'): ?>
                    <!-- Proceso de inscripción -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Confirmar Inscripción</h5>
                                </div>
                                <div class="card-body">
                                    <h4 class="mb-3"><?php echo $curso['nombre']; ?></h4>
                                    
                                    <div class="mb-4">
                                        <h6 class="text-muted">Datos del curso</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Código:</strong> <?php echo $curso['codigo']; ?></p>
                                                <p><strong>Categoría:</strong> <?php echo $curso['categoria_nombre']; ?></p>
                                                <p><strong>Duración:</strong> <?php echo $curso['duracion_horas']; ?> horas</p>
                                                <p><strong>Nivel:</strong> <?php echo ucfirst($curso['nivel']); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Fecha inicio:</strong> <?php echo date('d/m/Y', strtotime($curso['fecha_inicio'])); ?></p>
                                                <p><strong>Fecha fin:</strong> <?php echo date('d/m/Y', strtotime($curso['fecha_fin'])); ?></p>
                                                <p><strong>Modalidad:</strong> <?php echo ucfirst($curso['modalidad']); ?></p>
                                                <p><strong>Ubicación:</strong> <?php echo $curso['ubicacion'] ?: 'No especificada'; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <h6 class="text-muted">Tus datos</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Nombre:</strong> <?php echo $usuario['nombre'] . ' ' . $usuario['apellido']; ?></p>
                                                <p><strong>Email:</strong> <?php echo $usuario['email']; ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Teléfono:</strong> <?php echo $usuario['telefono'] ?: 'No especificado'; ?></p>
                                                <p><strong>Documento:</strong> <?php echo $usuario['documento_identidad'] ?: 'No especificado'; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info mb-4">
                                        <p class="mb-0"><i class="fas fa-info-circle me-2"></i> Al confirmar tu inscripción, aceptas los términos y condiciones del curso.</p>
                                    </div>
                                    
                                    <form action="inscripciones.php" method="post">
                                        <input type="hidden" name="curso_id" value="<?php echo $curso['id']; ?>">
                                        
                                        <div class="d-grid gap-2">
                                            <button type="submit" name="inscribir_curso" class="btn btn-primary">
                                                <i class="fas fa-check-circle me-1"></i> Confirmar Inscripción
                                            </button>
                                            <a href="inscripciones.php?action=explorar" class="btn btn-outline-secondary">
                                                <i class="fas fa-arrow-left me-1"></i> Regresar
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Resumen</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span>Precio del curso:</span>
                                        <strong>$<?php echo number_format($curso['precio'], 2); ?></strong>
                                    </div>
                                    
                                    <?php if ($curso['duracion_horas']): ?>
                                        <div class="d-flex justify-content-between mb-3">
                                            <span>Duración total:</span>
                                            <strong><?php echo $curso['duracion_horas']; ?> horas</strong>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <hr>
                                    
                                    <div class="d-flex justify-content-between mb-3">
                                        <span>Cupos disponibles:</span>
                                        <strong><?php echo $curso['cupo_maximo'] - $curso['total_inscritos']; ?> de <?php echo $curso['cupo_maximo']; ?></strong>
                                    </div>
                                    
                                    <div class="alert alert-warning mb-0">
                                        <small class="mb-0"><i class="fas fa-exclamation-triangle me-1"></i> Después de inscribirte, tendrás 48 horas para realizar el pago y confirmar tu cupo.</small>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($curso['descripcion'])): ?>
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Descripción del Curso</h5>
                                    </div>
                                    <div class="card-body">
                                        <p><?php echo nl2br($curso['descripcion']); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/scripts.js"></script>
</body>
</html>