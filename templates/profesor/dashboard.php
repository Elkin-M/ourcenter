<?php
$page_title = "Gestión de Salones";
$breadcrumb = [
    ['title' => 'Gestión de Salones']
];
$page_actions = '<a href="teacher_classroom_create.php" class="btn btn-primary">
    <i class="fas fa-plus-circle me-1"></i>Nuevo Salón
</a>';
require_once 'includes/teacher_header.php';
$teacher_id = $_SESSION['usuario_id'];

// Obtener filtros
$status_filter = $_GET['status'] ?? 'all';
$course_type_filter = $_GET['course_type'] ?? 'all';
$level_filter = $_GET['level'] ?? 'all';
$search = $_GET['search'] ?? '';

// Construir consulta con filtros
$where_conditions = ["s.teacher_id = ?"];
$params = [$teacher_id];

if ($status_filter !== 'all') {
    $where_conditions[] = "s.estado = ?";
    $params[] = $status_filter;
}

if ($course_type_filter !== 'all') {
    $where_conditions[] = "s.course_type = ?";
    $params[] = $course_type_filter;
}

if ($level_filter !== 'all') {
    $where_conditions[] = "s.level = ?";
    $params[] = $level_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(s.nombre LIKE ? OR s.codigo LIKE ? OR s.descripcion LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = implode(" AND ", $where_conditions);

// Obtener salones asignados al profesor
$classrooms_query = "SELECT s.*, 
    COUNT(DISTINCT u.id) as total_students,
    COUNT(DISTINCT CASE WHEN u.estado = 'activo' THEN u.id END) as active_students,
    c.nombre as course_name
    FROM salones s
    LEFT JOIN usuarios u ON u.salon_id = s.id
    LEFT JOIN cursos c ON s.curso_id = c.id
    WHERE $where_clause
    GROUP BY s.id
    ORDER BY s.fecha_creacion DESC";

$classrooms_stmt = $pdo->prepare($classrooms_query);
$classrooms_stmt->execute($params);
$classrooms = $classrooms_stmt->fetchAll();

// Obtener tipos de curso para el filtro (Kids, Teen, Adultos)
$course_types_query = "SELECT DISTINCT c.nombre, c.id FROM cursos c 
    JOIN salones s ON s.curso_id = c.id 
    WHERE s.teacher_id = ? 
    ORDER BY c.nombre";
$course_types_stmt = $pdo->prepare($course_types_query);
$course_types_stmt->execute([$teacher_id]);
$course_types = $course_types_stmt->fetchAll();

// Obtener niveles para el filtro (1-16)
$levels_query = "SELECT DISTINCT s.nivel FROM salones s 
    WHERE s.teacher_id = ? 
    ORDER BY s.nivel";
$levels_stmt = $pdo->prepare($levels_query);
$levels_stmt->execute([$teacher_id]);
$levels = $levels_stmt->fetchAll();
?>

<!-- Resumen rápido -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total de Salones</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($classrooms); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chalkboard fa-2x text-gray-300"></i>
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
                            Estudiantes Activos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php 
                            $active_students = array_sum(array_column($classrooms, 'active_students'));
                            echo $active_students;
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
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
                            Salones Activos
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php 
                            $active_classrooms = count(array_filter($classrooms, function($c) {
                                return $c['estado'] === 'activo';
                            }));
                            echo $active_classrooms;
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
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
                            Ocupación Promedio</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php 
                            $max_capacity = array_sum(array_column($classrooms, 'capacidad_maxima') ?: [0]);
                            $total_students = array_sum(array_column($classrooms, 'total_students'));
                            echo $max_capacity ? round(($total_students / $max_capacity) * 100) : 0;
                            ?>%
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-percentage fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter me-2"></i>Filtrar Salones</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="Nombre, código o descripción..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Estado</label>
                <select class="form-select" id="status" name="status">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>
                        Todos los estados
                    </option>
                    <option value="activo" <?php echo $status_filter === 'activo' ? 'selected' : ''; ?>>
                        Activos
                    </option>
                    <option value="inactivo" <?php echo $status_filter === 'inactivo' ? 'selected' : ''; ?>>
                        Inactivos
                    </option>
                    <option value="completado" <?php echo $status_filter === 'completado' ? 'selected' : ''; ?>>
                        Completados
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="course_type" class="form-label">Programa</label>
                <select class="form-select" id="course_type" name="course_type">
                    <option value="all" <?php echo $course_type_filter === 'all' ? 'selected' : ''; ?>>
                        Todos
                    </option>
                    <?php foreach ($course_types as $course_type): ?>
                        <option value="<?php echo $course_type['id']; ?>" 
                                <?php echo $course_type_filter == $course_type['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course_type['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="level" class="form-label">Nivel</label>
                <select class="form-select" id="level" name="level">
                    <option value="all" <?php echo $level_filter === 'all' ? 'selected' : ''; ?>>
                        Todos
                    </option>
                    <?php foreach ($levels as $level): ?>
                        <option value="<?php echo $level['level']; ?>" 
                                <?php echo $level_filter == $level['level'] ? 'selected' : ''; ?>>
                            Nivel <?php echo htmlspecialchars($level['level']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-filter me-1"></i> Filtrar
                </button>
                <a href="teacher_classrooms.php" class="btn btn-outline-secondary">
                    <i class="fas fa-undo me-1"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Salones -->
<div class="row">
    <?php if (empty($classrooms)): ?>
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <i class="fas fa-chalkboard fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No tienes salones asignados</h4>
                    <p class="text-muted mb-4">
                        Aún no se te han asignado salones para gestionar. Por favor contacta con la administración.
                    </p>
                    <a href="teacher_dashboard.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-home me-2"></i>Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($classrooms as $classroom): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card classroom-card shadow h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <?php echo htmlspecialchars($classroom['nombre']); ?>
                        </h6>
                        <span class="badge rounded-pill bg-<?php 
                            echo $classroom['estado'] === 'activo' ? 'success' : 
                                ($classroom['estado'] === 'inactivo' ? 'danger' : 'secondary'); 
                        ?>">
                            <?php echo ucfirst($classroom['estado']); ?>
                        </span>
                    </div>
                    
                    <div class="card-body">
                        <div class="classroom-meta mb-3">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="feature-icon bg-primary-light rounded-circle mb-2">
                                        <i class="fas fa-layer-group text-primary"></i>
                                    </div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($classroom['course_name']); ?></h6>
                                    <small class="text-muted">Programa</small>
                                </div>
                                <div class="col-4">
                                    <div class="feature-icon bg-success-light rounded-circle mb-2">
                                        <i class="fas fa-signal text-success"></i>
                                    </div>
                                    <h6 class="mb-0">Nivel <?php echo $classroom['level']; ?></h6>
                                    <small class="text-muted">Nivel</small>
                                </div>
                                <div class="col-4">
                                    <div class="feature-icon bg-info-light rounded-circle mb-2">
                                        <i class="fas fa-users text-info"></i>
                                    </div>
                                    <h6 class="mb-0"><?php echo $classroom['total_students']; ?>/<?php echo $classroom['capacidad_maxima']; ?></h6>
                                    <small class="text-muted">Estudiantes</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="classroom-details">
                            <div class="detail-item">
                                <i class="fas fa-calendar-alt text-muted me-2"></i>
                                <span class="detail-label">Horario:</span>
                                <span class="detail-value">
                                    <?php echo htmlspecialchars($classroom['horario'] ?? 'No especificado'); ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                <span class="detail-label">Ubicación:</span>
                                <span class="detail-value">
                                    <?php echo htmlspecialchars($classroom['ubicacion'] ?? 'No especificada'); ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-clock text-muted me-2"></i>
                                <span class="detail-label">Periodo:</span>
                                <span class="detail-value">
                                    <?php 
                                    if (!empty($classroom['fecha_inicio']) && !empty($classroom['fecha_fin'])) {
                                        echo date('d/m/Y', strtotime($classroom['fecha_inicio'])) . ' - ' . 
                                             date('d/m/Y', strtotime($classroom['fecha_fin']));
                                    } else {
                                        echo 'No especificado';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Progreso del curso -->
                        <?php if ($classroom['estado'] === 'activo'): ?>
                            <?php
                            // Calcular progreso basado en fechas
                            $progress = 0;
                            if (!empty($classroom['fecha_inicio']) && !empty($classroom['fecha_fin'])) {
                                $start = strtotime($classroom['fecha_inicio']);
                                $end = strtotime($classroom['fecha_fin']);
                                $now = time();
                                
                                if ($now >= $start && $now <= $end) {
                                    $total_duration = $end - $start;
                                    $elapsed = $now - $start;
                                    $progress = min(100, round(($elapsed / $total_duration) * 100));
                                } elseif ($now > $end) {
                                    $progress = 100;
                                }
                            }
                            ?>
                            <div class="mt-3">
                                <label class="d-flex justify-content-between align-items-center mb-1">
                                    <span>Progreso del curso</span>
                                    <span><?php echo $progress; ?>%</span>
                                </label>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?php echo $progress; ?>%" 
                                         aria-valuenow="<?php echo $progress; ?>" 
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-footer bg-transparent">
                        <div class="btn-group w-100" role="group">
                            <a href="teacher_classroom_detail.php?id=<?php echo $classroom['id']; ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-eye me-1"></i>Detalles
                            </a>
                            <a href="teacher_classroom_students.php?id=<?php echo $classroom['id']; ?>" 
                               class="btn btn-info">
                                <i class="fas fa-users me-1"></i>Estudiantes
                            </a>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-secondary dropdown-toggle" 
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="teacher_classroom_materials.php?id=<?php echo $classroom['id']; ?>">
                                            <i class="fas fa-book me-2"></i>Materiales
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="teacher_classroom_schedule.php?id=<?php echo $classroom['id']; ?>">
                                            <i class="fas fa-calendar-alt me-2"></i>Horario
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="teacher_classroom_edit.php?id=<?php echo $classroom['id']; ?>">
                                            <i class="fas fa-edit me-2"></i>Editar
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger btn-delete" 
                                           href="teacher_classroom_status.php?id=<?php echo $classroom['id']; ?>&action=<?php echo $classroom['estado'] === 'activo' ? 'deactivate' : 'activate'; ?>"
                                           data-item="<?php echo htmlspecialchars($classroom['nombre']); ?>">
                                            <i class="fas fa-<?php echo $classroom['estado'] === 'activo' ? 'pause' : 'play'; ?> me-2"></i>
                                            <?php echo $classroom['estado'] === 'activo' ? 'Desactivar' : 'Activar'; ?>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
    .breadcrumb-item+.breadcrumb-item::before{
        color:rgb(255, 255, 255);
    }
    .breadcrumb-item a {
    color:rgb(255, 255, 255);
}
    .breadcrumb-item.active {
    color: #ffffff !important;
}
    .navbar-nav .nav-link{
        color: #ffffff !important;
    }
.classroom-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border-radius: 0.5rem;
    overflow: hidden;
}

.classroom-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.classroom-meta {
    background-color: #f8f9fc;
    border-radius: 0.5rem;
    padding: 1rem 0.5rem;
    margin: -0.5rem -0.5rem 1rem -0.5rem;
}

.feature-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.bg-primary-light {
    background-color: rgba(78, 115, 223, 0.1);
}

.bg-success-light {
    background-color: rgba(28, 200, 138, 0.1);
}

.bg-info-light {
    background-color: rgba(54, 185, 204, 0.1);
}

.bg-warning-light {
    background-color: rgba(246, 194, 62, 0.1);
}

.classroom-details {
    margin-bottom: 1rem;
}

.detail-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 0.5rem;
    font-size: 0.85rem;
}

.detail-label {
    font-weight: 600;
    margin-right: 0.25rem;
}

.detail-value {
    color: #5a5c69;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.text-gray-300 {
    color: #dddfeb!important;
}

.text-gray-800 {
    color: #5a5c69!important;
}

.shadow {
    box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15)!important;
}

.font-weight-bold {
    font-weight: 700!important;
}
</style>

<script>
$(document).ready(function() {
    // Filtro en tiempo real
    let searchTimeout;
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            $('#search').closest('form').submit();
        }, 500);
    });
    
    // Cambio automático de filtros
    $('#status, #course_type, #level').on('change', function() {
        $(this).closest('form').submit();
    });
    
    // Activar tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
});
</script>

<?php require_once 'includes/teacher_footer.php'; ?>