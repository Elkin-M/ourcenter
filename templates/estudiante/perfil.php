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
$stmt = $conexion->prepare("
    SELECT u.*, r.nombre as rol_nombre 
    FROM usuarios u
    JOIN roles r ON u.rol_id = r.id
    WHERE u.id = ?
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

// Procesar actualización de datos personales
if (isset($_POST['actualizar_perfil'])) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $apellido = mysqli_real_escape_string($conexion, $_POST['apellido']);
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $direccion = mysqli_real_escape_string($conexion, $_POST['direccion']);
    $ciudad = mysqli_real_escape_string($conexion, $_POST['ciudad']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ? $_POST['fecha_nacimiento'] : null;
    
    $stmt = $conexion->prepare("
        UPDATE usuarios
        SET nombre = ?, apellido = ?, telefono = ?, direccion = ?, ciudad = ?, fecha_nacimiento = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssssssi", $nombre, $apellido, $telefono, $direccion, $ciudad, $fecha_nacimiento, $usuario_id);
    
    if ($stmt->execute()) {
        // Registrar la actividad
        registrarActividad($conexion, $usuario_id, 'actualización', 'usuarios', $usuario_id, 'Actualización de datos de perfil');
        
        $mensaje = 'Datos personales actualizados correctamente';
        $mensaje_tipo = 'success';
        
        // Actualizar datos en la sesión
        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['usuario_apellido'] = $apellido;
        
        // Refrescar datos del usuario
        $stmt = $conexion->prepare("
            SELECT u.*, r.nombre as rol_nombre 
            FROM usuarios u
            JOIN roles r ON u.rol_id = r.id
            WHERE u.id = ?
        ");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
    } else {
        $mensaje = 'Error al actualizar datos: ' . $conexion->error;
        $mensaje_tipo = 'danger';
    }
    $stmt->close();
}

// Procesar cambio de contraseña
if (isset($_POST['cambiar_password'])) {
    $password_actual = $_POST['password_actual'];
    $password_nuevo = $_POST['password_nuevo'];
    $password_confirmar = $_POST['password_confirmar'];
    
    // Verificar que la contraseña actual es correcta
    $stmt = $conexion->prepare("SELECT password FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario_data = $resultado->fetch_assoc();
    $stmt->close();
    
    if (password_verify($password_actual, $usuario_data['password'])) {
        // Verificar que las nuevas contraseñas coinciden
        if ($password_nuevo === $password_confirmar) {
            // Verificar complejidad de la contraseña
            if (strlen($password_nuevo) >= 8) {
                $password_hash = password_hash($password_nuevo, PASSWORD_DEFAULT);
                
                $stmt = $conexion->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $password_hash, $usuario_id);
                
                if ($stmt->execute()) {
                    // Registrar la actividad
                    registrarActividad($conexion, $usuario_id, 'actualización', 'usuarios', $usuario_id, 'Cambio de contraseña');
                    
                    $mensaje = 'Contraseña actualizada correctamente';
                    $mensaje_tipo = 'success';
                } else {
                    $mensaje = 'Error al actualizar la contraseña: ' . $conexion->error;
                    $mensaje_tipo = 'danger';
                }
                $stmt->close();
            } else {
                $mensaje = 'La nueva contraseña debe tener al menos 8 caracteres';
                $mensaje_tipo = 'warning';
            }
        } else {
            $mensaje = 'Las nuevas contraseñas no coinciden';
            $mensaje_tipo = 'warning';
        }
    } else {
        $mensaje = 'La contraseña actual es incorrecta';
        $mensaje_tipo = 'danger';
    }
}

// Procesar actualización de preferencias de notificación
if (isset($_POST['actualizar_notificaciones'])) {
    $recibir_email = isset($_POST['recibir_email']) ? 1 : 0;
    $recibir_sms = isset($_POST['recibir_sms']) ? 1 : 0;
    $recibir_whatsapp = isset($_POST['recibir_whatsapp']) ? 1 : 0;
    
    // Verificar si ya existen configuraciones para este usuario
    $stmt = $conexion->prepare("
        SELECT id FROM configuraciones 
        WHERE clave = CONCAT('notificacion_email_', ?)
    ");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        // Actualizar configuraciones existentes
        $stmt = $conexion->prepare("
            UPDATE configuraciones SET valor = ? 
            WHERE clave = CONCAT('notificacion_email_', ?)
        ");
        $stmt->bind_param("si", $recibir_email, $usuario_id);
        $stmt->execute();
        
        $stmt = $conexion->prepare("
            UPDATE configuraciones SET valor = ? 
            WHERE clave = CONCAT('notificacion_sms_', ?)
        ");
        $stmt->bind_param("si", $recibir_sms, $usuario_id);
        $stmt->execute();
        
        $stmt = $conexion->prepare("
            UPDATE configuraciones SET valor = ? 
            WHERE clave = CONCAT('notificacion_whatsapp_', ?)
        ");
        $stmt->bind_param("si", $recibir_whatsapp, $usuario_id);
        $stmt->execute();
    } else {
        // Insertar nuevas configuraciones
        $stmt = $conexion->prepare("
            INSERT INTO configuraciones (clave, valor, descripcion) 
            VALUES (CONCAT('notificacion_email_', ?), ?, 'Preferencia de notificación por email')
        ");
        $stmt->bind_param("is", $usuario_id, $recibir_email);
        $stmt->execute();
        
        $stmt = $conexion->prepare("
            INSERT INTO configuraciones (clave, valor, descripcion) 
            VALUES (CONCAT('notificacion_sms_', ?), ?, 'Preferencia de notificación por SMS')
        ");
        $stmt->bind_param("is", $usuario_id, $recibir_sms);
        $stmt->execute();
        
        $stmt = $conexion->prepare("
            INSERT INTO configuraciones (clave, valor, descripcion) 
            VALUES (CONCAT('notificacion_whatsapp_', ?), ?, 'Preferencia de notificación por WhatsApp')
        ");
        $stmt->bind_param("is", $usuario_id, $recibir_whatsapp);
        $stmt->execute();
    }
    
    // Registrar la actividad
    registrarActividad($conexion, $usuario_id, 'actualización', 'configuraciones', $usuario_id, 'Actualización de preferencias de notificación');
    
    $mensaje = 'Preferencias de notificación actualizadas correctamente';
    $mensaje_tipo = 'success';
    $stmt->close();
}

// Obtener preferencias de notificación
$preferencias = [
    'email' => false,
    'sms' => false,
    'whatsapp' => false
];

$stmt = $conexion->prepare("
    SELECT clave, valor FROM configuraciones 
    WHERE clave IN (?, ?, ?)
");
$email_key = 'notificacion_email_' . $usuario_id;
$sms_key = 'notificacion_sms_' . $usuario_id;
$whatsapp_key = 'notificacion_whatsapp_' . $usuario_id;
$stmt->bind_param("sss", $email_key, $sms_key, $whatsapp_key);
$stmt->execute();
$resultado = $stmt->get_result();

while ($row = $resultado->fetch_assoc()) {
    if ($row['clave'] === $email_key) {
        $preferencias['email'] = (bool)$row['valor'];
    } elseif ($row['clave'] === $sms_key) {
        $preferencias['sms'] = (bool)$row['valor'];
    } elseif ($row['clave'] === $whatsapp_key) {
        $preferencias['whatsapp'] = (bool)$row['valor'];
    }
}
$stmt->close();

// Incluir el header y el navbar
$current_page = 'Perfil';
// Incluir el header y sidebar
include './includes/estudiante-sidebar.php';
include './includes/estudiante-header.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Mi Perfil</h1>
    
    <?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $mensaje_tipo; ?> alert-dismissible fade show" role="alert">
        <?php echo $mensaje; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="https://via.placeholder.com/150" alt="Avatar" class="rounded-circle img-fluid" style="width: 150px;">
                    <h5 class="my-3"><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></h5>
                    <p class="text-muted mb-1"><?php echo htmlspecialchars($usuario['rol_nombre']); ?></p>
                    <p class="text-muted mb-4"><?php echo htmlspecialchars($usuario['ciudad'] ?? 'No especificado'); ?></p>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush rounded-3">
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <i class="fas fa-envelope fa-lg text-primary"></i>
                            <p class="mb-0"><?php echo htmlspecialchars($usuario['email']); ?></p>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <i class="fas fa-phone fa-lg text-success"></i>
                            <p class="mb-0"><?php echo htmlspecialchars($usuario['telefono'] ?? 'No especificado'); ?></p>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <i class="fas fa-id-card fa-lg text-warning"></i>
                            <p class="mb-0"><?php echo htmlspecialchars($usuario['documento_identidad'] ?? 'No especificado'); ?></p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <div class="card mb-4">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="datos-tab" data-bs-toggle="tab" data-bs-target="#datos" type="button" role="tab" aria-controls="datos" aria-selected="true">Datos Personales</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab" aria-controls="password" aria-selected="false">Cambiar Contraseña</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notificaciones-tab" data-bs-toggle="tab" data-bs-target="#notificaciones" type="button" role="tab" aria-controls="notificaciones" aria-selected="false">Preferencias de Notificación</button>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content" id="myTabContent">
                        <!-- Pestaña de Datos Personales -->
                        <div class="tab-pane fade show active" id="datos" role="tabpanel" aria-labelledby="datos-tab">
                            <form method="post" action="">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="apellido" class="form-label">Apellido</label>
                                        <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo htmlspecialchars($usuario['apellido']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled>
                                        <small class="text-muted">El email no se puede modificar</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="telefono" class="form-label">Teléfono</label>
                                        <input type="tel" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?>">
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="ciudad" class="form-label">Ciudad</label>
                                        <input type="text" class="form-control" id="ciudad" name="ciudad" value="<?php echo htmlspecialchars($usuario['ciudad'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($usuario['fecha_nacimiento'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="documento_identidad" class="form-label">Documento de Identidad</label>
                                        <input type="text" class="form-control" id="documento_identidad" value="<?php echo htmlspecialchars($usuario['documento_identidad'] ?? ''); ?>" disabled>
                                        <small class="text-muted">El documento no se puede modificar</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                                        <input type="text" class="form-control" id="tipo_documento" value="<?php echo htmlspecialchars($usuario['tipo_documento'] ?? ''); ?>" disabled>
                                        <small class="text-muted">El tipo de documento no se puede modificar</small>
                                    </div>
                                </div>
                                
                                <button type="submit" name="actualizar_perfil" class="btn btn-primary">Actualizar Datos</button>
                            </form>
                        </div>
                        
                        <!-- Pestaña de Cambiar Contraseña -->
                        <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="password_actual" class="form-label">Contraseña Actual</label>
                                    <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password_nuevo" class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="password_nuevo" name="password_nuevo" required>
                                    <small class="text-muted">La contraseña debe tener al menos 8 caracteres</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password_confirmar" class="form-label">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" required>
                                </div>
                                
                                <button type="submit" name="cambiar_password" class="btn btn-primary">Cambiar Contraseña</button>
                            </form>
                        </div>
                        
                        <!-- Pestaña de Preferencias de Notificación -->
                        <div class="tab-pane fade" id="notificaciones" role="tabpanel" aria-labelledby="notificaciones-tab">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="recibir_email" name="recibir_email" <?php echo $preferencias['email'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="recibir_email">Recibir notificaciones por Email</label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="recibir_sms" name="recibir_sms" <?php echo $preferencias['sms'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="recibir_sms">Recibir notificaciones por SMS</label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="recibir_whatsapp" name="recibir_whatsapp" <?php echo $preferencias['whatsapp'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="recibir_whatsapp">Recibir notificaciones por WhatsApp</label>
                                    </div>
                                </div>
                                
                                <button type="submit" name="actualizar_notificaciones" class="btn btn-primary">Guardar Preferencias</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Actividad Reciente</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Acción</th>
                                    <th>Detalles</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Obtener logs de actividad reciente del usuario
                                $stmt = $conexion->prepare("
                                    SELECT * FROM logs_actividad 
                                    WHERE usuario_id = ? 
                                    ORDER BY fecha_creacion DESC 
                                    LIMIT 5
                                ");
                                $stmt->bind_param("i", $usuario_id);
                                $stmt->execute();
                                $resultado = $stmt->get_result();
                                
                                if ($resultado->num_rows > 0) {
                                    while ($log = $resultado->fetch_assoc()) {
                                        echo '<tr>';
                                        echo '<td>' . date('d/m/Y H:i', strtotime($log['fecha_creacion'])) . '</td>';
                                        echo '<td>' . htmlspecialchars($log['accion']) . '</td>';
                                        echo '<td>' . htmlspecialchars($log['detalles']) . '</td>';
                                        echo '</tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="3" class="text-center">No hay actividad reciente</td></tr>';
                                }
                                $stmt->close();
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include './includes/estudiante-footer.php'; ?>
