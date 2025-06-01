<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    // Redirigir al login si no hay sesión
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Incluir el header y sidebar
include './includes/estudiante-sidebar.php';
include './includes/estudiante-header.php';

// Obtener el ID del usuario actual
$usuario_id = $_SESSION['usuario_id'];

// Función para sanitizar datos
function sanitizar($conexion, $dato) {
    return htmlspecialchars($conexion->real_escape_string($dato), ENT_QUOTES, 'UTF-8');
}

// Iniciar transacción para mejorar rendimiento en múltiples consultas
$conexion->begin_transaction();

try {
    // Obtener mis cursos activos con prepared statements
    $stmt = $conexion->prepare("
        SELECT c.id, c.nombre, c.codigo, c.imagen_url, c.horario, i.progreso_porcentaje
        FROM inscripciones i
        JOIN cursos c ON i.curso_id = c.id
        WHERE i.usuario_id = ? AND i.estado = 'confirmada' AND c.estado = 'activo'
        ORDER BY i.fecha_creacion DESC
        LIMIT 3
    ");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado_cursos = $stmt->get_result();
    $stmt->close();

    // Obtener próximos pagos - incluye fechas relativas y mejora la selección
    $stmt = $conexion->prepare("
        SELECT 
            c.nombre AS curso, 
            p.monto, 
            p.fecha_vencimiento, 
            p.id AS pago_id, 
            p.estado,
            DATEDIFF(p.fecha_vencimiento, CURDATE()) as dias_restantes
        FROM pagos p
        JOIN inscripciones i ON p.inscripcion_id = i.id
        JOIN cursos c ON i.curso_id = c.id
        WHERE i.usuario_id = ? AND (p.estado = 'pendiente' OR p.fecha_vencimiento >= CURDATE())
        ORDER BY p.fecha_vencimiento ASC
        LIMIT 3
    ");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado_pagos = $stmt->get_result();
    $stmt->close();

    // Obtener actividad reciente mejorada
    $stmt = $conexion->prepare("
        SELECT 
            la.accion, 
            la.fecha_creacion, 
            la.tabla_afectada,
            c.nombre AS curso,
            TIMESTAMPDIFF(MINUTE, la.fecha_creacion, NOW()) as minutos_transcurridos,
            TIMESTAMPDIFF(HOUR, la.fecha_creacion, NOW()) as horas_transcurridas,
            TIMESTAMPDIFF(DAY, la.fecha_creacion, NOW()) as dias_transcurridos
        FROM logs_actividad la
        LEFT JOIN inscripciones i ON la.registro_afectado_id = i.id AND la.tabla_afectada = 'inscripciones'
        LEFT JOIN cursos c ON i.curso_id = c.id
        WHERE la.usuario_id = ?
        ORDER BY la.fecha_creacion DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado_actividad = $stmt->get_result();
    $stmt->close();

    // Obtener datos para el gráfico de progreso con filtro para cursos activos
    $stmt = $conexion->prepare("
        SELECT c.nombre, i.progreso_porcentaje
        FROM inscripciones i
        JOIN cursos c ON i.curso_id = c.id
        WHERE i.usuario_id = ? AND i.estado = 'confirmada' AND c.estado = 'activo'
        ORDER BY i.progreso_porcentaje DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado_grafico = $stmt->get_result();
    $stmt->close();

    $datos_grafico = [];
    $colores_default = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'];
    $i = 0;
    
    while ($fila = $resultado_grafico->fetch_assoc()) {
        $color = !empty($fila['color_primario']) ? $fila['color_primario'] : $colores_default[$i % count($colores_default)];
        $datos_grafico[] = [
            'nombre' => $fila['nombre'],
            'progreso' => $fila['progreso_porcentaje'],
            'color' => $color
        ];
        $i++;
    }

    // Obtener estadísticas generales optimizadas en una sola consulta
    $stmt = $conexion->prepare("
        SELECT 
            (SELECT COUNT(*) FROM inscripciones WHERE usuario_id = ?) as total_inscripciones,
            (SELECT COUNT(*) FROM pagos p JOIN inscripciones i ON p.inscripcion_id = i.id 
             WHERE i.usuario_id = ? AND p.estado = 'pendiente') as pagos_pendientes,
            (SELECT ROUND(AVG(i.progreso_porcentaje), 1) FROM inscripciones i 
             WHERE i.usuario_id = ? AND i.estado = 'confirmada') as progreso_promedio
    ");
    $stmt->bind_param("iii", $usuario_id, $usuario_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $estadisticas = $result->fetch_assoc();
    $stmt->close();

    $total_inscripciones = $estadisticas['total_inscripciones'];
    $pagos_pendientes = $estadisticas['pagos_pendientes'];
    $progreso_promedio = $estadisticas['progreso_promedio'] ?: 0;

    // Obtener próxima clase
    $hoy = date('Y-m-d');
    $stmt = $conexion->prepare("
        SELECT 
            c.nombre AS curso_nombre,
            c.id AS curso_id,
            l.nombre AS leccion_nombre,
            DATE_FORMAT(l.fecha, '%d/%m/%Y') AS fecha_formateada,
            DATE_FORMAT(l.hora_inicio, '%H:%i') AS hora_inicio,
            DATE_FORMAT(l.hora_fin, '%H:%i') AS hora_fin,
            l.fecha,
            l.hora_inicio
        FROM lecciones l
        JOIN cursos c ON l.curso_id = c.id
        JOIN inscripciones i ON c.id = i.curso_id
        WHERE 
            i.usuario_id = ? 
            AND i.estado = 'confirmada' 
            AND c.estado = 'activo'
            AND (l.fecha > ? OR (l.fecha = ? AND l.hora_inicio > TIME(NOW())))
        ORDER BY l.fecha ASC, l.hora_inicio ASC
        LIMIT 1
    ");
    $stmt->bind_param("iss", $usuario_id, $hoy, $hoy);
    $stmt->execute();
    $proxima_clase = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Confirmar la transacción
    $conexion->commit();
} catch (Exception $e) {
    // Revertir en caso de error
    $conexion->rollback();
    error_log("Error en dashboard estudiante: " . $e->getMessage());
    $error_message = "Ha ocurrido un error al cargar el dashboard. Por favor, inténtelo de nuevo más tarde.";
}
?>


<div class="container-fluid py-4">
    <!-- Notificaciones -->
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Encabezado con nombre del estudiante -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Dashboard Estudiante</h1>
            <p class="text-muted">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Estudiante'); ?></p>
        </div>
        <div class="d-flex">
            <a href="calendario.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm me-2">
                <i class="fas fa-calendar fa-sm text-white-50"></i> Mi Calendario
            </a>
            <a href="mis-cursos.php" class="d-none d-sm-inline-block btn btn-sm btn-outline-primary shadow-sm">
                <i class="fas fa-graduation-cap fa-sm"></i> Mis Cursos
            </a>
        </div>
    </div>

    <!-- Próxima clase (si existe) -->
    <?php if (!empty($proxima_clase)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col-auto me-3">
                            <i class="fas fa-chalkboard-teacher fa-2x text-info"></i>
                        </div>
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Próxima Clase</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo htmlspecialchars($proxima_clase['curso_nombre']); ?> - 
                                <?php echo htmlspecialchars($proxima_clase['leccion_nombre']); ?>
                            </div>
                            <small class="text-muted">
                                <i class="far fa-calendar-alt me-1"></i><?php echo $proxima_clase['fecha_formateada']; ?> | 
                                <i class="far fa-clock me-1"></i><?php echo $proxima_clase['hora_inicio']; ?> - <?php echo $proxima_clase['hora_fin']; ?>
                            </small>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-sm btn-info" onclick="agregarAlCalendario('<?php echo addslashes($proxima_clase['curso_nombre']); ?>', '<?php echo addslashes($proxima_clase['leccion_nombre']); ?>', '<?php echo $proxima_clase['fecha']; ?>', '<?php echo $proxima_clase['hora_inicio']; ?>')">
                                <i class="fas fa-plus"></i> Añadir a mi calendario
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tarjetas informativas mejoradas con animación y accesibilidad -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 dashboard-card primary-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Mis Inscripciones</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 counter-number" data-target="<?php echo $total_inscripciones; ?>">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <a href="mis-cursos.php" class="stretched-link" aria-label="Ver mis inscripciones"></a>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 dashboard-card success-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Progreso Promedio</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800 counter-number" data-target="<?php echo $progreso_promedio; ?>" data-suffix="%">0</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2" role="progressbar" aria-valuenow="<?php echo $progreso_promedio; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar bg-success progress-animate" style="width: 0%;" data-width="<?php echo $progreso_promedio; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <a href="progreso.php" class="stretched-link" aria-label="Ver detalles de progreso"></a>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 dashboard-card warning-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pagos Pendientes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 counter-number" data-target="<?php echo $pagos_pendientes; ?>">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <a href="pagos.php?estado=pendiente" class="stretched-link" aria-label="Ver pagos pendientes"></a>
            </div>
        </div>
    </div>

    <!-- Contenido del Dashboard -->
    <div class="row">
        <!-- Mis Cursos Activos -->
        <div class="col-lg-6">
            <div class="card shadow mb-4 h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-gradient-light">
                    <h6 class="m-0 font-weight-bold text-primary">Mis Cursos Activos</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="dropdownMenuLink">
                            <a class="dropdown-item" href="mis-cursos.php"><i class="fas fa-list fa-sm fa-fw me-2 text-gray-400"></i> Ver todos</a>
                            <a class="dropdown-item" href="mis-cursos.php?view=calendar"><i class="fas fa-calendar fa-sm fa-fw me-2 text-gray-400"></i> Ver calendario</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="inscripciones.php"><i class="fas fa-plus fa-sm fa-fw me-2 text-gray-400"></i> Nueva inscripción</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($resultado_cursos->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Curso</th>
                                        <th scope="col">Horario</th>
                                        <th scope="col">Progreso</th>
                                        <th scope="col" class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($curso = $resultado_cursos->fetch_assoc()): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($curso['nombre']); ?></td>
                                            <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($curso['horario']); ?></span></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                        <div class="progress-bar progress-animate" role="progressbar" 
                                                             style="width: 0%;" 
                                                             data-width="<?php echo $curso['progreso_porcentaje']; ?>%" 
                                                             aria-valuenow="<?php echo $curso['progreso_porcentaje']; ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="counter-number" data-target="<?php echo $curso['progreso_porcentaje']; ?>" data-suffix="%">0</span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <a href="detalle-curso.php?id=<?php echo $curso['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> Ver
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="mis-cursos.php" class="btn btn-outline-primary btn-sm">Ver todos mis cursos <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <img src="assets/img/illustrations/no-courses.svg" alt="Sin cursos" class="img-fluid mb-3" style="max-height: 150px;">
                            <p>No estás inscrito en ningún curso actualmente.</p>
                            <a href="inscripciones.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Explorar Cursos
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Progreso de Cursos -->
        <div class="col-lg-6">
            <div class="card shadow mb-4 h-100">
                <div class="card-header py-3 bg-gradient-light">
                    <h6 class="m-0 font-weight-bold text-primary">Progreso de Cursos</h6>
                </div>
                <div class="card-body">
                    <?php if (count($datos_grafico) > 0): ?>
                        <div class="chart-bar">
                            <canvas id="graficoProgresoCursos" height="300"></canvas>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-center flex-wrap gap-2 mt-3">
                            <?php foreach ($datos_grafico as $curso): ?>
                            <div class="d-flex align-items-center me-3">
                                <div class="color-indicator me-1" style="background-color: <?php echo $curso['color']; ?>"></div>
                                <span class="small text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($curso['nombre']); ?>">
                                    <?php echo htmlspecialchars($curso['nombre']); ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <img src="assets/img/illustrations/no-data.svg" alt="Sin datos" class="img-fluid mb-3" style="max-height: 150px;">
                            <p>No hay datos de progreso disponibles.</p>
                            <a href="inscripciones.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Inscríbete en cursos
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Próximos Pagos -->
        <div class="col-lg-6">
            <div class="card shadow mb-4 h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-gradient-light">
                    <h6 class="m-0 font-weight-bold text-primary">Próximos Pagos</h6>
                    <a href="pagos.php" class="btn btn-sm btn-primary">
                        Ver todos <i class="fas fa-arrow-right ms-1 fa-sm"></i>
                    </a>
                </div>
                <div class="card-body">
                    <?php if ($resultado_pagos->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Curso</th>
                                        <th scope="col">Monto</th>
                                        <th scope="col">Vencimiento</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col" class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($pago = $resultado_pagos->fetch_assoc()): 
                                        $badge_class = 'bg-warning text-dark';
                                        $urgente = false;
                                        
                                        if ($pago['estado'] == 'completado') {
                                            $badge_class = 'bg-success';
                                        } elseif ($pago['estado'] == 'fallido') {
                                            $badge_class = 'bg-danger';
                                        } elseif ($pago['dias_restantes'] <= 3 && $pago['dias_restantes'] >= 0) {
                                            $badge_class = 'bg-danger text-white';
                                            $urgente = true;
                                        } elseif ($pago['dias_restantes'] < 0) {
                                            $badge_class = 'bg-dark text-white';
                                        }
                                    ?>
                                        <tr<?php echo $urgente ? ' class="table-danger"' : ''; ?>>
                                            <td><?php echo htmlspecialchars($pago['curso']); ?></td>
                                            <td class="fw-bold">$<?php echo number_format($pago['monto'], 2); ?></td>
                                            <td>
                                                <div><?php echo date('d/m/Y', strtotime($pago['fecha_vencimiento'])); ?></div>
                                                <?php if ($pago['dias_restantes'] > 0): ?>
                                                    <small class="text-muted">En <?php echo $pago['dias_restantes']; ?> días</small>
                                                <?php elseif ($pago['dias_restantes'] == 0): ?>
                                                    <small class="text-danger fw-bold">¡Hoy!</small>
                                                <?php else: ?>
                                                    <small class="text-danger fw-bold">Vencido (<?php echo abs($pago['dias_restantes']); ?> días)</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $badge_class; ?>">
                                                    <?php echo ucfirst($pago['estado']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($pago['estado'] == 'pendiente'): ?>
                                                    <a href="realizar-pago.php?id=<?php echo $pago['pago_id']; ?>" class="btn btn-sm btn-success">
                                                        <i class="fas fa-money-bill"></i> Pagar
                                                    </a>
                                                <?php else: ?>
                                                    <a href="pagos.php?id=<?php echo $pago['pago_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> Ver
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <img src="assets/img/illustrations/payment-success.svg" alt="Sin pagos pendientes" class="img-fluid mb-3" style="max-height: 150px;">
                            <p class="text-success fw-bold">¡No tienes pagos pendientes!</p>
                            <p class="text-muted">Todos tus pagos están al día.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Actividad Reciente -->
        <div class="col-lg-6">
            <div class="card shadow mb-4 h-100">
                <div class="card-header py-3 bg-gradient-light">
                    <h6 class="m-0 font-weight-bold text-primary">Actividad Reciente</h6>
                </div>
                <div class="card-body">
                    <?php if ($resultado_actividad->num_rows > 0): ?>
                        <div class="timeline">
                            <?php while ($actividad = $resultado_actividad->fetch_assoc()): 
                                // Determinar texto relativo de tiempo
                                $tiempo_texto = '';
                                if ($actividad['minutos_transcurridos'] < 60) {
                                    $tiempo_texto = 'Hace ' . $actividad['minutos_transcurridos'] . ' minutos';
                                    if ($actividad['minutos_transcurridos'] <= 1) {
                                        $tiempo_texto = 'Hace un momento';
                                    }
                                } elseif ($actividad['horas_transcurridas'] < 24) {
                                    $tiempo_texto = 'Hace ' . $actividad['horas_transcurridas'] . ' horas';
                                    if ($actividad['horas_transcurridas'] == 1) {
                                        $tiempo_texto = 'Hace 1 hora';
                                    }
                                } else {
                                    $tiempo_texto = 'Hace ' . $actividad['dias_transcurridos'] . ' días';
                                    if ($actividad['dias_transcurridos'] == 1) {
                                        $tiempo_texto = 'Ayer';
                                    }
                                }
                                
                                // Determinar ícono según el tipo de actividad
                                $icono = 'fas fa-history';
                                if (strpos($actividad['tabla_afectada'], 'inscripcion') !== false) {
                                    $icono = 'fas fa-user-graduate';
                                } elseif (strpos($actividad['tabla_afectada'], 'pago') !== false) {
                                    $icono = 'fas fa-money-bill';
                                } elseif (strpos($actividad['tabla_afectada'], 'curso') !== false) {
                                    $icono = 'fas fa-book';
                                } elseif (strpos($actividad['tabla_afectada'], 'usuario') !== false) {
                                    $icono = 'fas fa-user-edit';
                                }
                            ?>
                                <div class="timeline-item">
                                    <div class="timeline-icon">
                                        <i class="<?php echo $icono; ?>"></i>
                                    </div>
                                    <div class="timeline-date" title="<?php echo date('d/m/Y H:i', strtotime($actividad['fecha_creacion'])); ?>">
                                        <?php echo $tiempo_texto; ?>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($actividad['accion']); ?></h6>
                                        <?php if (!empty($actividad['curso'])): ?>
                                            <p class="mb-0 small">Curso: <?php echo htmlspecialchars($actividad['curso']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="actividad.php" class="btn btn-outline-primary btn-sm">Ver todo mi historial <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <img src="assets/img/illustrations/activity.svg" alt="Sin actividad" class="img-fluid mb-3" style="max-height: 150px;">
                            <p>No hay actividad reciente para mostrar.</p>
                            <p class="text-muted small">Tu actividad aparecerá aquí a medida que uses la plataforma.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas mejoradas con tooltips y botones más descriptivos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 bg-gradient-light">
                    <h6 class="m-0 font-weight-bold text-primary">Acciones Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="inscripciones.php?action=nueva" class="card text-center p-4 h-100 shadow-sm hover-card" data-bs-toggle="tooltip" data-bs-placement="top" title="Inscríbete en un nuevo curso">
                                <div class="icon-circle bg-primary">
                                    <i class="fas fa-plus-circle text-white"></i>
                                </div>
                                <div class="mt-3">
                                    <h5>Nueva Inscripción</h5>
                                    <p class="text-muted small mb-0">Explora nuestros cursos disponibles</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="pagos.php?action=pendientes" class="card text-center p-4 h-100 shadow-sm hover-card" data-bs-toggle="tooltip" data-bs-placement="top" title="Gestiona tus pagos pendientes">
                                <div class="icon-circle bg-warning">
                                    <i class="fas fa-credit-card text-white"></i>
                                </div>
                                <div class="mt-3">
                                    <h5>Realizar Pago</h5>
                                    <p class="text-muted small mb-0">Gestiona tus pagos pendientes</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="soporte.php" class="card text-center p-4 h-100 shadow-sm hover-card" data-bs-toggle="tooltip" data-bs-placement="top" title="Obtén ayuda con tus dudas">
                                <div class="icon-circle bg-info">
                                    <i class="fas fa-headset text-white"></i>
                                </div>
                                <div class="mt-3">
                                    <h5>Contactar Soporte</h5>
                                    <p class="text-muted small mb-0">Te ayudamos con tus dudas</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="perfil.php" class="card text-center p-4 h-100 shadow-sm hover-card" data-bs-toggle="tooltip" data-bs-placement="top" title="Actualiza tu información personal">
                                <div class="icon-circle bg-success">
                                    <i class="fas fa-user-edit text-white"></i>
                                </div>
                                <div class="mt-3">
                                    <h5>Editar Perfil</h5>
                                    <p class="text-muted small mb-0">Actualiza tu información personal</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recursos Populares -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 bg-gradient-light">
                    <h6 class="m-0 font-weight-bold text-primary">Recursos Populares</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-circle bg-light me-3">
                                            <i class="fas fa-book text-primary"></i>
                                        </div>
                                        <h5 class="card-title mb-0">Biblioteca Digital</h5>
                                    </div>
                                    <p class="card-text">Accede a nuestra colección de libros, artículos y recursos académicos.</p>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="biblioteca.php" class="btn btn-outline-primary btn-sm">Explorar Biblioteca</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-circle bg-light me-3">
                                            <i class="fas fa-users text-info"></i>
                                        </div>
                                        <h5 class="card-title mb-0">Foros de Discusión</h5>
                                    </div>
                                    <p class="card-text">Participa en conversaciones y resuelve dudas con otros estudiantes.</p>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="foros.php" class="btn btn-outline-info btn-sm">Unirse a Foros</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-circle bg-light me-3">
                                            <i class="fas fa-question-circle text-success"></i>
                                        </div>
                                        <h5 class="card-title mb-0">Preguntas Frecuentes</h5>
                                    </div>
                                    <p class="card-text">Encuentra respuestas a las preguntas más comunes sobre cursos y plataforma.</p>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="faqs.php" class="btn btn-outline-success btn-sm">Ver FAQ</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts específicos para esta página -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips de Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Animar contadores numéricos
    const counterNumbers = document.querySelectorAll('.counter-number');
    counterNumbers.forEach(counter => {
        const target = parseFloat(counter.getAttribute('data-target'));
        const suffix = counter.getAttribute('data-suffix') || '';
        const duration = 1000; // duración de la animación en ms
        const steps = 50; // cantidad de pasos para la animación
        const increment = target / steps;
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                counter.textContent = target.toFixed(1).replace(/\.0$/, '') + suffix;
                clearInterval(timer);
            } else {
                counter.textContent = current.toFixed(1).replace(/\.0$/, '') + suffix;
            }
        }, duration / steps);
    });
    
    // Animar barras de progreso
    const progressBars = document.querySelectorAll('.progress-animate');
    progressBars.forEach(bar => {
        const targetWidth = bar.getAttribute('data-width');
        setTimeout(() => {
            bar.style.width = targetWidth;
        }, 200);
    });
    
    // Gráfico de progreso de cursos
    const ctx = document.getElementById('graficoProgresoCursos');
    if (ctx) {
        const datosGrafico = <?php echo json_encode($datos_grafico); ?>;
        
        if (datosGrafico && datosGrafico.length > 0) {
            const nombres = datosGrafico.map(item => item.nombre);
            const progresos = datosGrafico.map(item => item.progreso);
            const colores = datosGrafico.map(item => item.color);
            
            new Chart(ctx, {
                type: 'horizontalBar',
                data: {
                    labels: nombres,
                    datasets: [{
                        label: 'Progreso (%)',
                        data: progresos,
                        backgroundColor: colores,
                        borderColor: colores.map(color => color),
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                display: true
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Progreso: ${context.raw}%`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }
});

// Función para añadir evento al calendario personal
function agregarAlCalendario(curso, leccion, fecha, hora) {
    // Detectar qué tipo de dispositivo está usando el usuario
    const userAgent = navigator.userAgent.toLowerCase();
    const isiOS = /iphone|ipad|ipod/.test(userAgent);
    const isAndroid = /android/.test(userAgent);
    
    // Título para el evento
    const titulo = `Clase: ${curso} - ${leccion}`;
    const fechaHora = `${fecha}T${hora}:00`;
    
    // Crear enlace según el dispositivo
    let calendarUrl = '';
    
    if (isiOS) {
        // Formato para iOS
        calendarUrl = `webcal://ourcenter.com/generar-ical.php?titulo=${encodeURIComponent(titulo)}&fecha=${encodeURIComponent(fechaHora)}`;
    } else if (isAndroid) {
        // Formato para Google Calendar (Android)
        calendarUrl = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(titulo)}&dates=${encodeURIComponent(fechaHora.replace(/[-:]/g,''))}/`;
    } else {
        // Descargar archivo .ics genérico
        calendarUrl = `descargar-ical.php?titulo=${encodeURIComponent(titulo)}&fecha=${encodeURIComponent(fechaHora)}`;
    }
    
    // Abrir la URL en una nueva ventana
    window.open(calendarUrl, '_blank');
}
</script>

<!-- Estilos específicos para esta página -->
<style>
/* Animaciones y hover effects */
.dashboard-card {
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
}

.hover-card {
    transition: all 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

/* Mejorar las tarjetas con bordes más definidos */
.primary-card {
    border-left: 5px solid #4e73df !important;
}

.success-card {
    border-left: 5px solid #1cc88a !important;
}

.warning-card {
    border-left: 5px solid #f6c23e !important;
}

/* Indicador de color para la leyenda del gráfico */
.color-indicator {
    width: 15px;
    height: 15px;
    border-radius: 3px;
    display: inline-block;
}

/* Timeline para la actividad reciente */
.timeline {
    position: relative;
    padding: 0;
    list-style: none;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    margin-bottom: 20px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-icon {
    position: absolute;
    left: 0;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: #f8f9fc;
    text-align: center;
    line-height: 30px;
    border: 1px solid #e3e6f0;
}

.timeline-date {
    font-size: 12px;
    color: #858796;
    margin-bottom: 5px;
}

.timeline-content {
    background-color: #f8f9fc;
    padding: 10px 15px;
    border-radius: 5px;
    border-left: 3px solid #4e73df;
}

/* Círculos de iconos */
.icon-circle {
    height: 60px;
    width: 60px;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-circle i {
    font-size: 24px;
}

/* Progress bar animation */
.progress-bar {
    transition: width 1s ease-in-out;
}
</style>

<?php
// Incluir el footer
include './includes/estudiante-footer.php';
?>