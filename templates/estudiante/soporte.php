<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');
require_once '../../config/conexion-courses.php';
require_once '../../server/config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$mensaje = '';
$mensaje_tipo = '';

// Obtener información del usuario
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

// Procesar envío de formulario de contacto
if (isset($_POST['enviar_solicitud'])) {
    $asunto = mysqli_real_escape_string($conexion, $_POST['asunto']);
    $mensaje_text = mysqli_real_escape_string($conexion, $_POST['mensaje']);
    $curso_id = !empty($_POST['curso_id']) ? intval($_POST['curso_id']) : null;
    
    $stmt = $conexion->prepare("
        INSERT INTO solicitudes_contacto (nombre, email, telefono, asunto, mensaje, curso_interes_id, estado) 
        VALUES (?, ?, ?, ?, ?, ?, 'nuevo')
    ");
    $nombre_completo = $usuario['nombre'] . ' ' . $usuario['apellido'];
    $stmt->bind_param("sssssi", $nombre_completo, $usuario['email'], $usuario['telefono'], $asunto, $mensaje_text, $curso_id);
    
    if ($stmt->execute()) {
        $solicitud_id = $stmt->insert_id;
        
        // Registrar la actividad
        registrarActividad($conexion, $usuario_id, 'creación', 'solicitudes_contacto', $solicitud_id, 'Nueva solicitud de soporte');
        
        $mensaje = 'Solicitud enviada correctamente. Nos pondremos en contacto contigo pronto.';
        $mensaje_tipo = 'success';
    } else {
        $mensaje = 'Error al enviar la solicitud: ' . $conexion->error;
        $mensaje_tipo = 'danger';
    }
    $stmt->close();
}

// Obtener cursos del usuario para la selección
$stmt = $conexion->prepare("
    SELECT c.id, c.nombre, c.codigo
    FROM cursos c
    JOIN inscripciones i ON c.id = i.curso_id
    WHERE i.usuario_id = ?
    ORDER BY c.nombre
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$cursos_usuario = $stmt->get_result();
$stmt->close();

// Obtener solicitudes anteriores del usuario
$stmt = $conexion->prepare("
    SELECT s.*, c.nombre as curso_nombre,
           u.nombre as asignado_nombre, u.apellido as asignado_apellido
    FROM solicitudes_contacto s
    LEFT JOIN cursos c ON s.curso_interes_id = c.id
    LEFT JOIN usuarios u ON s.asignado_a = u.id
    WHERE s.email = ?
    ORDER BY s.fecha_creacion DESC
");
$stmt->bind_param("s", $usuario['email']);
$stmt->execute();
$solicitudes = $stmt->get_result();
$stmt->close();

// Obtener detalles de una solicitud específica si se proporciona un ID
$solicitud_detalles = null;
$seguimiento = null;

if (isset($_GET['ver_solicitud']) && is_numeric($_GET['ver_solicitud'])) {
    $solicitud_id = intval($_GET['ver_solicitud']);
    
    // Verificar que la solicitud pertenece al usuario
    $stmt = $conexion->prepare("
        SELECT s.*, c.nombre as curso_nombre,
               u.nombre as asignado_nombre, u.apellido as asignado_apellido
        FROM solicitudes_contacto s
        LEFT JOIN cursos c ON s.curso_interes_id = c.id
        LEFT JOIN usuarios u ON s.asignado_a = u.id
        WHERE s.id = ? AND s.email = ?
    ");
    $stmt->bind_param("is", $solicitud_id, $usuario['email']);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $solicitud_detalles = $resultado->fetch_assoc();
        
        // Obtener el seguimiento de la solicitud
        $stmt = $conexion->prepare("
            SELECT ss.*, u.nombre, u.apellido, u.rol_id
            FROM seguimiento_solicitudes ss
            JOIN usuarios u ON ss.usuario_id = u.id
            WHERE ss.solicitud_id = ?
            ORDER BY ss.fecha_creacion
        ");
        $stmt->bind_param("i", $solicitud_id);
        $stmt->execute();
        $seguimiento = $stmt->get_result();
    }
    $stmt->close();
}

// Agregar comentario a una solicitud
if (isset($_POST['agregar_comentario']) && isset($_POST['solicitud_id']) && is_numeric($_POST['solicitud_id'])) {
    $solicitud_id = intval($_POST['solicitud_id']);
    $comentario = mysqli_real_escape_string($conexion, $_POST['comentario']);
    
    // Verificar que la solicitud pertenece al usuario
    $stmt = $conexion->prepare("SELECT id FROM solicitudes_contacto WHERE id = ? AND email = ?");
    $stmt->bind_param("is", $solicitud_id, $usuario['email']);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $stmt = $conexion->prepare("
            INSERT INTO seguimiento_solicitudes (solicitud_id, usuario_id, comentario)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iis", $solicitud_id, $usuario_id, $comentario);
        
        if ($stmt->execute()) {
            // Actualizar estado de la solicitud si estaba cerrada
            $stmt = $conexion->prepare("
                UPDATE solicitudes_contacto
                SET estado = 'en_proceso'
                WHERE id = ? AND estado = 'completado'
            ");
            $stmt->bind_param("i", $solicitud_id);
            $stmt->execute();
            
            // Registrar la actividad
            registrarActividad($conexion, $usuario_id, 'creación', 'seguimiento_solicitudes', $stmt->insert_id, 'Nuevo comentario en solicitud');
            
            $mensaje = 'Comentario agregado correctamente';
            $mensaje_tipo = 'success';
            
            // Redirigir para ver la solicitud actualizada
            header('Location: soporte.php?ver_solicitud=' . $solicitud_id);
            exit;
        } else {
            $mensaje = 'Error al agregar el comentario: ' . $conexion->error;
            $mensaje_tipo = 'danger';
        }
    } else {
        $mensaje = 'No tienes permisos para comentar en esta solicitud';
        $mensaje_tipo = 'danger';
    }
    $stmt->close();
}

// Incluir el header y el navbar
$current_page = 'Soporte';
include './includes/estudiante-sidebar.php';
include './includes/estudiante-header.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Soporte</h1>
    
    <?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $mensaje_tipo; ?> alert-dismissible fade show" role="alert">
        <?php echo $mensaje; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if (isset($solicitud_detalles)): ?>
        <!-- Detalle de la solicitud -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    Solicitud #<?php echo $solicitud_detalles['id']; ?>: 
                    <?php echo htmlspecialchars($solicitud_detalles['asunto']); ?>
                </h5>
                <a href="soporte.php" class="btn btn-sm btn-outline-secondary">Volver a solicitudes</a>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($solicitud_detalles['fecha_creacion'])); ?></p>
                        <p class="mb-1"><strong>Estado:</strong> 
                            <span class="badge bg-
                                <?php 
                                switch($solicitud_detalles['estado']) {
                                    case 'nuevo': echo 'primary'; break;
                                    case 'en_proceso': echo 'warning'; break;
                                    case 'completado': echo 'success'; break;
                                    case 'cancelado': echo 'danger'; break;
                                    default: echo 'secondary';
                                }
                                ?>">
                                <?php 
                                switch($solicitud_detalles['estado']) {
                                    case 'nuevo': echo 'Nuevo'; break;
                                    case 'en_proceso': echo 'En proceso'; break;
                                    case 'completado': echo 'Completado'; break;
                                    case 'cancelado': echo 'Cancelado'; break;
                                    default: echo $solicitud_detalles['estado'];
                                }
                                ?>
                            </span>
                        </p>
                        <?php if ($solicitud_detalles['asignado_a']): ?>
                            <p class="mb-1"><strong>Asignado a:</strong> <?php echo htmlspecialchars($solicitud_detalles['asignado_nombre'] . ' ' . $solicitud_detalles['asignado_apellido']); ?></p>
                        <?php endif; ?>
                        <?php if ($solicitud_detalles['curso_nombre']): ?>
                            <p class="mb-1"><strong>Curso relacionado:</strong> <?php echo htmlspecialchars($solicitud_detalles['curso_nombre']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h6>Mensaje original:</h6>
                                <p><?php echo nl2br(htmlspecialchars($solicitud_detalles['mensaje'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <!-- Seguimiento de la solicitud -->
                <h5 class="mb-3">Seguimiento</h5>
                
                <?php if ($seguimiento && $seguimiento->num_rows > 0): ?>
                    <div class="timeline mb-4">
                        <?php while ($comentario = $seguimiento->fetch_assoc()): ?>
                            <div class="timeline-item">
                                <div class="card mb-3 <?php echo ($comentario['usuario_id'] == $usuario_id) ? 'border-primary' : ''; ?>">
                                    <div class="card-header bg-light d-flex justify-content-between">
                                        <span>
                                            <strong><?php echo htmlspecialchars($comentario['nombre'] . ' ' . $comentario['apellido']); ?></strong>
                                            <?php if ($comentario['rol_id'] == 1): ?>
                                                <span class="badge bg-info">Administrador</span>
                                            <?php endif; ?>
                                        </span>
                                        <small><?php echo date('d/m/Y H:i', strtotime($comentario['fecha_creacion'])); ?></small>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No hay comentarios de seguimiento aún.</p>
                <?php endif; ?>
                
                <?php if ($solicitud_detalles['estado'] != 'cancelado'): ?>
                    <!-- Formulario para agregar comentario -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Agregar comentario</h6>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <input type="hidden" name="solicitud_id" value="<?php echo $solicitud_detalles['id']; ?>">
                                <div class="mb-3">
                                    <textarea class="form-control" name="comentario" rows="3" required placeholder="Escribe tu comentario aquí..."></textarea>
                                </div>
                                <button type="submit" name="agregar_comentario" class="btn btn-primary">Enviar comentario</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <!-- Formulario de contacto -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Contactar Soporte</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="asunto" class="form-label">Asunto</label>
                                <input type="text" class="form-control" id="asunto" name="asunto" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="curso_id" class="form-label">Curso relacionado (opcional)</label>
                                <select class="form-select" id="curso_id" name="curso_id">
                                    <option value="">-- Seleccionar curso --</option>
                                    <?php while ($curso = $cursos_usuario->fetch_assoc()): ?>
                                        <option value="<?php echo $curso['id']; ?>">
                                            <?php echo htmlspecialchars($curso['codigo'] . ' - ' . $curso['nombre']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mensaje" class="form-label">Mensaje</label>
                                <textarea class="form-control" id="mensaje" name="mensaje" rows="6" required></textarea>
                            </div>
                            
                            <button type="submit" name="enviar_solicitud" class="btn btn-primary">Enviar solicitud</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Preguntas frecuentes -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Preguntas Frecuentes</h5>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="accordionFAQ">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                        ¿Cómo puedo inscribirme en un curso?
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        Para inscribirte en un curso, navega hasta la sección "Inscripciones" en el menú principal y haz clic en "Nueva Inscripción". Allí podrás ver los cursos disponibles y seguir el proceso de inscripción.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        ¿Cómo puedo realizar un pago?
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        Puedes realizar pagos a través de la sección "Pagos" en el menú principal. Allí encontrarás tus pagos pendientes y las opciones disponibles para realizarlos.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        ¿Cómo actualizo mi información personal?
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        Para actualizar tu información personal, dirígete a la sección "Mi Perfil" en el menú principal. Allí podrás editar tus datos personales, cambiar tu contraseña y configurar tus preferencias de notificación.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        ¿Cómo puedo ver mi horario de clases?
                                    </button>
                                </h2>
                                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        Puedes consultar tu horario de clases en la sección "Calendario" del menú principal. Allí verás todos tus cursos y horarios organizados por fecha.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFive">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                        ¿Qué hago si olvidé mi contraseña?
                                    </button>
                                </h2>
                                <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        Si olvidaste tu contraseña, haz clic en el enlace "¿Olvidaste tu contraseña?" en la página de inicio de sesión. Se te enviará un correo electrónico con instrucciones para restablecer tu contraseña.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mis solicitudes anteriores -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Mis Solicitudes</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($solicitudes->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Fecha</th>
                                            <th>Asunto</th>
                                            <th>Curso</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($solicitud = $solicitudes->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $solicitud['id']; ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($solicitud['fecha_creacion'])); ?></td>
                                                <td><?php echo htmlspecialchars($solicitud['asunto']); ?></td>
                                                <td><?php echo htmlspecialchars($solicitud['curso_nombre'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="badge bg-
                                                        <?php 
                                                        switch($solicitud['estado']) {
                                                            case 'nuevo': echo 'primary'; break;
                                                            case 'en_proceso': echo 'warning'; break;
                                                            case 'completado': echo 'success'; break;
                                                            case 'cancelado': echo 'danger'; break;
                                                            default: echo 'secondary';
                                                        }
                                                        ?>">
                                                        <?php 
                                                        switch($solicitud['estado']) {
                                                            case 'nuevo': echo 'Nuevo'; break;
                                                            case 'en_proceso': echo 'En proceso'; break;
                                                            case 'completado': echo 'Completado'; break;
                                                            case 'cancelado': echo 'Cancelado'; break;
                                                            default: echo $solicitud['estado'];
                                                        }
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="soporte.php?ver_solicitud=<?php echo $solicitud['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> Ver
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No has enviado ninguna solicitud aún.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include './includes/estudiante-footer.php'; ?>