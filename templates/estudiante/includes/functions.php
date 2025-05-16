<?php
/**
 * Archivo de funciones generales para el sistema OurCenter
 */

// Configuración de conexión a la base de datos
function conectarDB() {
    $host = 'localhost';
    $db = 'ourcenter';
    $user = 'root';
    $password = '';
    
    try {
        $conexion = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $password);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexion;
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

// Función para iniciar sesión
function iniciarSesion($email, $password) {
    $conexion = conectarDB();
    
    $query = "SELECT u.*, r.nombre as rol_nombre FROM usuarios u 
              JOIN roles r ON u.rol_id = r.id 
              WHERE u.email = :email AND u.estado = 'activo'";
    $stmt = $conexion->prepare($query);
    $stmt->execute(['email' => $email]);
    
    if ($stmt->rowCount() > 0) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $usuario['password'])) {
            // Actualizar última sesión
            $updateQuery = "UPDATE usuarios SET ultima_sesion = NOW() WHERE id = :id";
            $updateStmt = $conexion->prepare($updateQuery);
            $updateStmt->execute(['id' => $usuario['id']]);
            
            // Registrar actividad
            registrarActividad($usuario['id'], 'inicio_sesion', 'usuarios', $usuario['id'], 'Inicio de sesión exitoso');
            
            // Iniciar sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_rol'] = $usuario['rol_nombre'];
            $_SESSION['usuario_rol_id'] = $usuario['rol_id'];
            
            return true;
        }
    }
    return false;
}

// Función para registrar actividad en el sistema
function registrarActividad($usuario_id, $accion, $tabla_afectada = null, $registro_afectado_id = null, $detalles = null) {
    $conexion = conectarDB();
    
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'desconocida';
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'desconocido';
    
    $query = "INSERT INTO logs_actividad (usuario_id, accion, tabla_afectada, registro_afectado_id, detalles, ip_usuario, user_agent) 
              VALUES (:usuario_id, :accion, :tabla_afectada, :registro_afectado_id, :detalles, :ip_usuario, :user_agent)";
    $stmt = $conexion->prepare($query);
    $stmt->execute([
        'usuario_id' => $usuario_id,
        'accion' => $accion,
        'tabla_afectada' => $tabla_afectada,
        'registro_afectado_id' => $registro_afectado_id,
        'detalles' => $detalles,
        'ip_usuario' => $ip,
        'user_agent' => $user_agent
    ]);
}

// Función para verificar si el usuario está logueado
function verificarSesion() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Función para verificar permisos de rol
function verificarRol($roles_permitidos) {
    if (!isset($_SESSION['usuario_rol']) || !in_array($_SESSION['usuario_rol'], $roles_permitidos)) {
        header('Location: unauthorized.php');
        exit;
    }
}

// Función para obtener los cursos del usuario actual
function obtenerCursosUsuario($usuario_id, $filtro = null) {
    $conexion = conectarDB();
    
    $condicionFiltro = "";
    if ($filtro) {
        switch ($filtro) {
            case 'activos':
                $condicionFiltro = " AND c.estado = 'activo'";
                break;
            case 'pendientes':
                $condicionFiltro = " AND c.estado = 'en_inscripcion'";
                break;
            case 'completados':
                $condicionFiltro = " AND c.estado = 'completado'";
                break;
        }
    }
    
    $query = "SELECT c.*, i.estado as inscripcion_estado, i.progreso_porcentaje, i.id as inscripcion_id, 
              cat.nombre as categoria_nombre
              FROM inscripciones i
              JOIN cursos c ON i.curso_id = c.id
              LEFT JOIN categorias_cursos cat ON c.categoria_id = cat.id
              WHERE i.usuario_id = :usuario_id $condicionFiltro
              ORDER BY c.fecha_inicio ASC";
    
    $stmt = $conexion->prepare($query);
    $stmt->execute(['usuario_id' => $usuario_id]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para obtener un curso específico
function obtenerCurso($curso_id) {
    $conexion = conectarDB();
    
    $query = "SELECT c.*, cat.nombre as categoria_nombre
              FROM cursos c
              LEFT JOIN categorias_cursos cat ON c.categoria_id = cat.id
              WHERE c.id = :curso_id";
    
    $stmt = $conexion->prepare($query);
    $stmt->execute(['curso_id' => $curso_id]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Función para obtener todas las inscripciones del usuario
function obtenerInscripcionesUsuario($usuario_id) {
    $conexion = conectarDB();
    
    $query = "SELECT i.*, c.nombre as curso_nombre, c.imagen_url, c.precio, c.fecha_inicio, c.fecha_fin
              FROM inscripciones i
              JOIN cursos c ON i.curso_id = c.id
              WHERE i.usuario_id = :usuario_id
              ORDER BY i.fecha_creacion DESC";
    
    $stmt = $conexion->prepare($query);
    $stmt->execute(['usuario_id' => $usuario_id]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para obtener los pagos del usuario
function obtenerPagosUsuario($usuario_id, $filtro = null) {
    $conexion = conectarDB();
    
    $condicionFiltro = "";
    if ($filtro) {
        switch ($filtro) {
            case 'pendientes':
                $condicionFiltro = " AND p.estado = 'pendiente'";
                break;
            case 'completados':
                $condicionFiltro = " AND p.estado = 'completado'";
                break;
        }
    }
    
    $query = "SELECT p.*, i.usuario_id, c.nombre as curso_nombre, c.imagen_url
              FROM pagos p
              JOIN inscripciones i ON p.inscripcion_id = i.id
              JOIN cursos c ON i.curso_id = c.id
              WHERE i.usuario_id = :usuario_id $condicionFiltro
              ORDER BY p.fecha_creacion DESC";
    
    $stmt = $conexion->prepare($query);
    $stmt->execute(['usuario_id' => $usuario_id]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para registrar un nuevo pago
function registrarPago($inscripcion_id, $monto, $metodo_pago, $referencia = null) {
    $conexion = conectarDB();
    
    $query = "INSERT INTO pagos (inscripcion_id, monto, metodo_pago, referencia_externa, estado)
              VALUES (:inscripcion_id, :monto, :metodo_pago, :referencia, 'pendiente')";
    
    $stmt = $conexion->prepare($query);
    $resultado = $stmt->execute([
        'inscripcion_id' => $inscripcion_id,
        'monto' => $monto,
        'metodo_pago' => $metodo_pago,
        'referencia' => $referencia
    ]);
    
    if ($resultado) {
        $pago_id = $conexion->lastInsertId();
        
        // Obtener el usuario_id de la inscripción
        $queryUsuario = "SELECT usuario_id FROM inscripciones WHERE id = :inscripcion_id";
        $stmtUsuario = $conexion->prepare($queryUsuario);
        $stmtUsuario->execute(['inscripcion_id' => $inscripcion_id]);
        $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            registrarActividad($usuario['usuario_id'], 'registro_pago', 'pagos', $pago_id, 'Pago registrado para inscripción #' . $inscripcion_id);
        }
        
        return $pago_id;
    }
    
    return false;
}

// Función para actualizar perfil de usuario
function actualizarPerfil($usuario_id, $datos) {
    $conexion = conectarDB();
    
    $campos = [];
    $valores = [];
    
    // Preparar campos y valores para la actualización
    foreach ($datos as $campo => $valor) {
        if (in_array($campo, ['nombre', 'apellido', 'telefono', 'direccion', 'ciudad'])) {
            $campos[] = "$campo = :$campo";
            $valores[$campo] = $valor;
        }
    }
    
    if (empty($campos)) {
        return false;
    }
    
    $query = "UPDATE usuarios SET " . implode(", ", $campos) . " WHERE id = :usuario_id";
    $valores['usuario_id'] = $usuario_id;
    
    $stmt = $conexion->prepare($query);
    $resultado = $stmt->execute($valores);
    
    if ($resultado) {
        registrarActividad($usuario_id, 'actualizacion_perfil', 'usuarios', $usuario_id, 'Perfil actualizado');
    }
    
    return $resultado;
}

// Función para cambiar contraseña
function cambiarPassword($usuario_id, $password_actual, $password_nuevo) {
    $conexion = conectarDB();
    
    // Verificar contraseña actual
    $queryVerificar = "SELECT password FROM usuarios WHERE id = :usuario_id";
    $stmtVerificar = $conexion->prepare($queryVerificar);
    $stmtVerificar->execute(['usuario_id' => $usuario_id]);
    $usuario = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario || !password_verify($password_actual, $usuario['password'])) {
        return false;
    }
    
    // Actualizar contraseña
    $password_hash = password_hash($password_nuevo, PASSWORD_DEFAULT);
    $queryActualizar = "UPDATE usuarios SET password = :password WHERE id = :usuario_id";
    $stmtActualizar = $conexion->prepare($queryActualizar);
    $resultado = $stmtActualizar->execute([
        'password' => $password_hash,
        'usuario_id' => $usuario_id
    ]);
    
    if ($resultado) {
        registrarActividad($usuario_id, 'cambio_password', 'usuarios', $usuario_id, 'Contraseña actualizada');
    }
    
    return $resultado;
}

// Función para crear una nueva solicitud de contacto/soporte
function crearSolicitudContacto($datos, $usuario_id = null) {
    $conexion = conectarDB();
    
    $query = "INSERT INTO solicitudes_contacto (nombre, email, telefono, asunto, mensaje, curso_interes_id)
              VALUES (:nombre, :email, :telefono, :asunto, :mensaje, :curso_interes_id)";
    
    $stmt = $conexion->prepare($query);
    $resultado = $stmt->execute([
        'nombre' => $datos['nombre'],
        'email' => $datos['email'],
        'telefono' => $datos['telefono'] ?? null,
        'asunto' => $datos['asunto'] ?? null,
        'mensaje' => $datos['mensaje'],
        'curso_interes_id' => $datos['curso_interes_id'] ?? null
    ]);
    
    if ($resultado) {
        $solicitud_id = $conexion->lastInsertId();
        if ($usuario_id) {
            registrarActividad($usuario_id, 'solicitud_contacto', 'solicitudes_contacto', $solicitud_id, 'Nueva solicitud de contacto creada');
        }
        return $solicitud_id;
    }
    
    return false;
}

// Función para obtener las solicitudes de contacto del usuario
function obtenerSolicitudesUsuario($usuario_id) {
    $conexion = conectarDB();
    
    $query = "SELECT s.*, c.nombre as curso_nombre
              FROM solicitudes_contacto s
              LEFT JOIN cursos c ON s.curso_interes_id = c.id
              WHERE s.email = (SELECT email FROM usuarios WHERE id = :usuario_id)
              ORDER BY s.fecha_creacion DESC";
    
    $stmt = $conexion->prepare($query);
    $stmt->execute(['usuario_id' => $usuario_id]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para obtener los cursos disponibles para inscripción
function obtenerCursosDisponibles($filtros = []) {
    $conexion = conectarDB();
    
    $condiciones = ["c.estado = 'en_inscripcion'"];
    $parametros = [];
    
    if (!empty($filtros['categoria'])) {
        $condiciones[] = "c.categoria_id = :categoria";
        $parametros['categoria'] = $filtros['categoria'];
    }
    
    if (!empty($filtros['modalidad'])) {
        $condiciones[] = "c.modalidad = :modalidad";
        $parametros['modalidad'] = $filtros['modalidad'];
    }
    
    if (!empty($filtros['nivel'])) {
        $condiciones[] = "c.nivel = :nivel";
        $parametros['nivel'] = $filtros['nivel'];
    }
    
    $where = implode(" AND ", $condiciones);
    
    $query = "SELECT c.*, cat.nombre as categoria_nombre
              FROM cursos c
              LEFT JOIN categorias_cursos cat ON c.categoria_id = cat.id
              WHERE $where
              ORDER BY c.fecha_inicio ASC";
    
    $stmt = $conexion->prepare($query);
    $stmt->execute($parametros);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para crear una nueva inscripción
function crearInscripcion($usuario_id, $curso_id, $metodo_inscripcion = 'online') {
    $conexion = conectarDB();
    
    // Verificar si ya existe una inscripción para este usuario y curso
    $queryVerificar = "SELECT id FROM inscripciones WHERE usuario_id = :usuario_id AND curso_id = :curso_id";
    $stmtVerificar = $conexion->prepare($queryVerificar);
    $stmtVerificar->execute([
        'usuario_id' => $usuario_id,
        'curso_id' => $curso_id
    ]);
    
    if ($stmtVerificar->rowCount() > 0) {
        return ['status' => false, 'mensaje' => 'Ya existe una inscripción para este curso'];
    }
    
    // Crear la inscripción
    $queryInscripcion = "INSERT INTO inscripciones (usuario_id, curso_id, metodo_inscripcion)
                      VALUES (:usuario_id, :curso_id, :metodo_inscripcion)";
    
    $stmtInscripcion = $conexion->prepare($queryInscripcion);
    $resultado = $stmtInscripcion->execute([
        'usuario_id' => $usuario_id,
        'curso_id' => $curso_id,
        'metodo_inscripcion' => $metodo_inscripcion
    ]);
    
    if ($resultado) {
        $inscripcion_id = $conexion->lastInsertId();
        
        registrarActividad($usuario_id, 'nueva_inscripcion', 'inscripciones', $inscripcion_id, 'Nueva inscripción al curso #' . $curso_id);
        
        // Obtener el precio del curso para el pago
        $queryCurso = "SELECT precio FROM cursos WHERE id = :curso_id";
        $stmtCurso = $conexion->prepare($queryCurso);
        $stmtCurso->execute(['curso_id' => $curso_id]);
        $curso = $stmtCurso->fetch(PDO::FETCH_ASSOC);
        
        if ($curso) {
            return [
                'status' => true,
                'inscripcion_id' => $inscripcion_id,
                'precio' => $curso['precio']
            ];
        }
        
        return ['status' => true, 'inscripcion_id' => $inscripcion_id];
    }
    
    return ['status' => false, 'mensaje' => 'No se pudo crear la inscripción'];
}

// Función para obtener todas las categorías de cursos
function obtenerCategorias() {
    $conexion = conectarDB();
    
    $query = "SELECT * FROM categorias_cursos ORDER BY nombre ASC";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para obtener datos del usuario
function obtenerDatosUsuario($usuario_id) {
    $conexion = conectarDB();
    
    $query = "SELECT * FROM usuarios WHERE id = :usuario_id";
    $stmt = $conexion->prepare($query);
    $stmt->execute(['usuario_id' => $usuario_id]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Función para obtener próximos eventos del calendario
function obtenerEventosCalendario($usuario_id) {
    $conexion = conectarDB();
    
    $query = "SELECT c.id, c.nombre, c.fecha_inicio, c.fecha_fin, c.horario, c.ubicacion, c.modalidad
              FROM inscripciones i
              JOIN cursos c ON i.curso_id = c.id
              WHERE i.usuario_id = :usuario_id AND c.estado = 'activo'
              ORDER BY c.fecha_inicio ASC";
    
    $stmt = $conexion->prepare($query);
    $stmt->execute(['usuario_id' => $usuario_id]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Función para formatear fechas
function formatearFecha($fecha, $incluir_hora = false) {
    if (!$fecha) return 'N/A';
    
    $timestamp = strtotime($fecha);
    if ($incluir_hora) {
        return date('d/m/Y H:i', $timestamp);
    }
    return date('d/m/Y', $timestamp);
}

// Función para formatear moneda
function formatearMoneda($monto) {
    return number_format($monto, 2, ',', '.');
}

// Función para generar una clave única para un archivo
function generarClaveArchivo($prefijo = '') {
    return $prefijo . uniqid() . '_' . time();
}

// Función para subir un archivo
function subirArchivo($archivo, $directorio = 'uploads', $tipos_permitidos = ['image/jpeg', 'image/png', 'application/pdf']) {
    // Verificar que el directorio existe, si no, crearlo
    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
    }
    
    // Verificar que sea un tipo permitido
    if (!in_array($archivo['type'], $tipos_permitidos)) {
        return ['error' => 'Tipo de archivo no permitido'];
    }
    
    // Generar nombre único
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nombre_archivo = generarClaveArchivo() . '.' . $extension;
    $ruta_completa = $directorio . '/' . $nombre_archivo;
    
    // Mover el archivo
    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        return ['nombre' => $nombre_archivo, 'ruta' => $ruta_completa];
    }
    
    return ['error' => 'No se pudo subir el archivo'];
}