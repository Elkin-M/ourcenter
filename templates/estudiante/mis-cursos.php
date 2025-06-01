<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');
require_once '../../config/conexion-courses.php';
// require_once 'includes/functions.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$current_page = "Mis Cursos";

// Obtener los cursos del usuario
$sql = "SELECT c.*, i.estado as estado_inscripcion, i.progreso_porcentaje, i.id as inscripcion_id 
        FROM cursos c 
        INNER JOIN inscripciones i ON c.id = i.curso_id 
        WHERE i.usuario_id = ? 
        ORDER BY c.fecha_inicio DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

// Obtener la lista de todos los cursos para el filtro
$sql_categorias = "SELECT * FROM categorias_cursos ORDER BY nombre";
$result_categorias = $conexion->query($sql_categorias);

// Filtros
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';

// Título de la página
$page_title = "Mis Cursos";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Cursos - OurCenter</title>
<?php
// Incluir el header y sidebar
include './includes/estudiante-sidebar.php';
include './includes/estudiante-header.php';
?>
    <style>
        .curso-card {
            transition: transform 0.3s ease;
            margin-bottom: 20px;
            height: 100%;
        }
        .curso-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .progress {
            height: 10px;
        }
        .course-image {
            height: 160px;
            object-fit: cover;
        }
        .badge-pill {
            padding: 5px 10px;
            border-radius: 15px;
        }
        .view-toggle .btn {
            border-radius: 0;
        }
        .view-toggle .btn:first-child {
            border-top-left-radius: 4px;
            border-bottom-left-radius: 4px;
        }
        .view-toggle .btn:last-child {
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
        }
        #calendar {
            margin-top: 20px;
        }
        .fc-event {
            cursor: pointer;
        }
    </style>
</head>
<body>    
    <div class="container-fluid">
        <div class="row">            
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                    <h1 class="h2">Mis Cursos</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="view-toggle btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-primary active" id="gridViewBtn">
                                <i class="fas fa-th-large"></i> Tarjetas
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="calendarViewBtn">
                                <i class="fas fa-calendar"></i> Calendario
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="row g-3" method="GET" action="">
                                    <div class="col-md-4">
                                        <label for="estado" class="form-label">Estado</label>
                                        <select class="form-select" id="estado" name="estado">
                                            <option value="">Todos</option>
                                            <option value="pendiente" <?php echo ($filtro_estado == 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                            <option value="confirmada" <?php echo ($filtro_estado == 'confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                                            <option value="completada" <?php echo ($filtro_estado == 'completada') ? 'selected' : ''; ?>>Completada</option>
                                            <option value="cancelada" <?php echo ($filtro_estado == 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="categoria" class="form-label">Categoría</label>
                                        <select class="form-select" id="categoria" name="categoria">
                                            <option value="">Todas</option>
                                            <?php while($categoria = $result_categorias->fetch_assoc()): ?>
                                                <option value="<?php echo $categoria['id']; ?>" <?php echo ($filtro_categoria == $categoria['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                                        <a href="mis-cursos.php" class="btn btn-outline-secondary">Limpiar</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="gridView">
                    <div class="row">
                        <?php 
                        if ($result->num_rows > 0) {
                            while($curso = $result->fetch_assoc()) {
                                // Aplicar filtros
                                if ($filtro_estado && $curso['estado_inscripcion'] != $filtro_estado) continue;
                                if ($filtro_categoria && $curso['categoria_id'] != $filtro_categoria) continue;
                                
                                // Preparar colores y etiquetas según el estado
                                $estado_badge_class = '';
                                switch($curso['estado_inscripcion']) {
                                    case 'pendiente':
                                        $estado_badge_class = 'bg-warning text-dark';
                                        break;
                                    case 'confirmada':
                                        $estado_badge_class = 'bg-success';
                                        break;
                                    case 'completada':
                                        $estado_badge_class = 'bg-info';
                                        break;
                                    case 'cancelada':
                                        $estado_badge_class = 'bg-danger';
                                        break;
                                }
                                
                                // Formatear fechas
                                $fecha_inicio = date('d/m/Y', strtotime($curso['fecha_inicio']));
                                $fecha_fin = date('d/m/Y', strtotime($curso['fecha_fin']));
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card curso-card">
                                <img src="../.<?php echo $curso['imagen_url'] ? $curso['imagen_url'] : 'assets/img/course-default.jpg'; ?>" class="card-img-top course-image" alt="<?php echo htmlspecialchars($curso['nombre']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($curso['nombre']); ?></h5>
                                    <span class="badge badge-pill <?php echo $estado_badge_class; ?> mb-2">
                                        <?php echo ucfirst($curso['estado_inscripcion']); ?>
                                    </span>
                                    
                                    <p class="card-text small mb-2">
                                        <i class="fas fa-calendar-alt me-1"></i> <?php echo $fecha_inicio; ?> - <?php echo $fecha_fin; ?>
                                    </p>
                                    <p class="card-text small mb-2">
                                        <i class="fas fa-clock me-1"></i> <?php echo htmlspecialchars($curso['horario']); ?>
                                    </p>
                                    <p class="card-text small mb-3">
                                        <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($curso['ubicacion']); ?>
                                    </p>
                                    
                                    <div class="mb-3">
                                        <label class="form-label small">Progreso del curso:</label>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: <?php echo $curso['progreso_porcentaje']; ?>%" 
                                                aria-valuenow="<?php echo $curso['progreso_porcentaje']; ?>" aria-valuemin="0" aria-valuemax="100">
                                                <?php echo $curso['progreso_porcentaje']; ?>%
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <a href="detalles-curso.php?id=<?php echo $curso['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i> Ver detalles
                                        </a>
                                        <a href="material-curso.php?id=<?php echo $curso['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-book me-1"></i> Material
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                            }
                        } else {
                        ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                No tienes cursos inscritos actualmente. 
                                <a href="inscripciones.php?action=nueva" class="alert-link">Inscríbete en un curso aquí</a>.
                            </div>
                        </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
                
                <div id="calendarView" style="display: none;">
                    <div id="calendar"></div>
                </div>
        </div>
    </div>
    
    <?php include 'includes/estudiante-footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar vistas de grid y calendario
            const gridViewBtn = document.getElementById('gridViewBtn');
            const calendarViewBtn = document.getElementById('calendarViewBtn');
            const gridView = document.getElementById('gridView');
            const calendarView = document.getElementById('calendarView');
            
            gridViewBtn.addEventListener('click', function() {
                gridView.style.display = 'block';
                calendarView.style.display = 'none';
                gridViewBtn.classList.add('active');
                calendarViewBtn.classList.remove('active');
            });
            
            calendarViewBtn.addEventListener('click', function() {
                gridView.style.display = 'none';
                calendarView.style.display = 'block';
                gridViewBtn.classList.remove('active');
                calendarViewBtn.classList.add('active');
                
                if (!calendarInitialized) {
                    initializeCalendar();
                    calendarInitialized = true;
                }
            });
            
            // Inicializar calendario solo cuando se necesita
            let calendarInitialized = false;
            
            function initializeCalendar() {
                const calendarEl = document.getElementById('calendar');
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                    },
                    locale: 'es',
                    events: [
                        <?php 
                        // Reiniciar el puntero de resultados
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        while($curso = $result->fetch_assoc()) {
                            // Aplicar filtros
                            if ($filtro_estado && $curso['estado_inscripcion'] != $filtro_estado) continue;
                            if ($filtro_categoria && $curso['categoria_id'] != $filtro_categoria) continue;
                            
                            // Determinar color según estado
                            $color = '';
                            switch($curso['estado_inscripcion']) {
                                case 'pendiente': $color = '#ffc107'; break;
                                case 'confirmada': $color = '#28a745'; break;
                                case 'completada': $color = '#17a2b8'; break;
                                case 'cancelada': $color = '#dc3545'; break;
                            }
                            
                            echo "{";
                            echo "title: '" . addslashes($curso['nombre']) . "',";
                            echo "start: '" . $curso['fecha_inicio'] . "',";
                            echo "end: '" . $curso['fecha_fin'] . "',";
                            echo "url: 'detalles-curso.php?id=" . $curso['id'] . "',";
                            echo "backgroundColor: '" . $color . "',";
                            echo "borderColor: '" . $color . "'";
                            echo "},";
                        }
                        ?>
                    ],
                    eventClick: function(info) {
                        info.jsEvent.preventDefault();
                        window.location.href = info.event.url;
                    }
                });
                calendar.render();
            }
        });
    </script>
</body>
</html>