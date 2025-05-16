<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');
require_once '../../config/conexion-courses.php';
// require_once 'includes/funciones.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$current_page = basename($_SERVER['PHP_SELF']);

// Obtener información del usuario
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario_result = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Función para obtener eventos del usuario desde inscripciones y cursos
function obtenerEventosUsuario($conexion, $usuario_id) {
    $eventos = [];
    
    // Obtener cursos inscritos
    $stmt = $conexion->prepare("SELECT i.id as inscripcion_id, c.id as curso_id, c.nombre, c.descripcion, 
                           c.fecha_inicio, c.fecha_fin, c.horario, c.ubicacion, c.modalidad,
                           i.estado as inscripcion_estado
                           FROM inscripciones i 
                           JOIN cursos c ON i.curso_id = c.id 
                           WHERE i.usuario_id = ? AND i.estado != 'cancelada'");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Añadir evento de inicio de curso
        if (!empty($row['fecha_inicio'])) {
            $eventos[] = [
                'title' => $row['nombre'],
                'start' => $row['fecha_inicio'],
                'description' => 'Inicio del curso: ' . $row['descripcion'],
                'backgroundColor' => '#007bff',
                'borderColor' => '#007bff',
                'url' => 'mis-cursos.php?action=detalle&id=' . $row['curso_id'],
                'extendedProps' => [
                    'tipo' => 'curso_inicio',
                    'modalidad' => $row['modalidad'],
                    'ubicacion' => $row['ubicacion'],
                    'horario' => $row['horario']
                ]
            ];
        }
        
        // Añadir evento de fin de curso
        if (!empty($row['fecha_fin'])) {
            $eventos[] = [
                'title' => 'Finaliza: ' . $row['nombre'],
                'start' => $row['fecha_fin'],
                'description' => 'Finalización del curso: ' . $row['descripcion'],
                'backgroundColor' => '#6c757d',
                'borderColor' => '#6c757d',
                'url' => 'mis-cursos.php?action=detalle&id=' . $row['curso_id'],
                'extendedProps' => [
                    'tipo' => 'curso_fin',
                    'modalidad' => $row['modalidad']
                ]
            ];
        }
        
        // Generar eventos para las sesiones basados en el horario (suponiendo formato "Lunes y Miércoles 15:00-17:00")
        if (!empty($row['horario']) && !empty($row['fecha_inicio']) && !empty($row['fecha_fin'])) {
            $dias_semana = [
                'lunes' => 1,
                'martes' => 2,
                'miércoles' => 3,
                'jueves' => 4,
                'viernes' => 5,
                'sábado' => 6,
                'domingo' => 0
            ];
            
            // Extraer días de la semana del horario
            $dias_curso = [];
            foreach ($dias_semana as $dia_nombre => $dia_num) {
                if (stripos($row['horario'], $dia_nombre) !== false) {
                    $dias_curso[] = $dia_num;
                }
            }
            
            // Extraer horario (asumiendo formato HH:MM-HH:MM)
            preg_match('/(\d{1,2}):(\d{2})-(\d{1,2}):(\d{2})/', $row['horario'], $matches);
            if (count($matches) >= 5) {
                $hora_inicio = $matches[1] . ':' . $matches[2];
                $hora_fin = $matches[3] . ':' . $matches[4];
                
                // Crear eventos recurrentes para cada sesión
                $fecha_inicio = new DateTime($row['fecha_inicio']);
                $fecha_fin = new DateTime($row['fecha_fin']);
                $intervalo = new DateInterval('P1D'); // Intervalo de 1 día
                
                $periodo = new DatePeriod($fecha_inicio, $intervalo, $fecha_fin);
                
                foreach ($periodo as $fecha) {
                    $dia_semana = $fecha->format('w'); // 0 (domingo) a 6 (sábado)
                    
                    if (in_array($dia_semana, $dias_curso)) {
                        $fecha_sesion = $fecha->format('Y-m-d');
                        $inicio_sesion = $fecha_sesion . 'T' . $hora_inicio;
                        $fin_sesion = $fecha_sesion . 'T' . $hora_fin;
                        
                        $eventos[] = [
                            'title' => 'Sesión: ' . $row['nombre'],
                            'start' => $inicio_sesion,
                            'end' => $fin_sesion,
                            'backgroundColor' => '#28a745',
                            'borderColor' => '#28a745',
                            'url' => 'mis-cursos.php?action=detalle&id=' . $row['curso_id'],
                            'extendedProps' => [
                                'tipo' => 'sesion',
                                'modalidad' => $row['modalidad'],
                                'ubicacion' => $row['ubicacion']
                            ]
                        ];
                    }
                }
            }
        }
    }
    $stmt->close();
    
    // Obtener fechas de pago
    $stmt = $conexion->prepare("SELECT p.id, p.monto, p.estado, p.fecha_vencimiento, p.fecha_creacion,
                           c.nombre as curso_nombre, i.curso_id
                           FROM pagos p
                           JOIN inscripciones i ON p.inscripcion_id = i.id
                           JOIN cursos c ON i.curso_id = c.id
                           WHERE i.usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['fecha_vencimiento']) && $row['estado'] == 'pendiente') {
            $eventos[] = [
                'title' => 'Pago pendiente: $' . number_format($row['monto'], 2),
                'start' => $row['fecha_vencimiento'],
                'description' => 'Vencimiento de pago para: ' . $row['curso_nombre'],
                'backgroundColor' => '#dc3545',
                'borderColor' => '#dc3545',
                'url' => 'pagos.php?action=detalle&id=' . $row['id'],
                'extendedProps' => [
                    'tipo' => 'pago_vencimiento'
                ]
            ];
        }
        
        if ($row['estado'] == 'completado') {
            $eventos[] = [
                'title' => 'Pago realizado: $' . number_format($row['monto'], 2),
                'start' => $row['fecha_creacion'],
                'description' => 'Pago completado para: ' . $row['curso_nombre'],
                'backgroundColor' => '#20c997',
                'borderColor' => '#20c997',
                'url' => 'pagos.php?action=detalle&id=' . $row['id'],
                'extendedProps' => [
                    'tipo' => 'pago_realizado'
                ]
            ];
        }
    }
    $stmt->close();
    
    return $eventos;
}

// Función para filtrar eventos por categoría
function filtrarEventos($eventos, $categoria) {
    if ($categoria == 'todos') {
        return $eventos;
    }
    
    $eventos_filtrados = [];
    foreach ($eventos as $evento) {
        switch ($categoria) {
            case 'cursos':
                if (strpos($evento['extendedProps']['tipo'], 'curso_') === 0 || $evento['extendedProps']['tipo'] == 'sesion') {
                    $eventos_filtrados[] = $evento;
                }
                break;
            case 'pagos':
                if (strpos($evento['extendedProps']['tipo'], 'pago_') === 0) {
                    $eventos_filtrados[] = $evento;
                }
                break;
            default:
                if ($evento['extendedProps']['tipo'] == $categoria) {
                    $eventos_filtrados[] = $evento;
                }
                break;
        }
    }
    
    return $eventos_filtrados;
}

// Obtener eventos para el usuario actual
$eventos = obtenerEventosUsuario($conexion, $usuario_id);

// Filtrar eventos si se especifica una categoría
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : 'todos';
$eventos_filtrados = filtrarEventos($eventos, $categoria);

// Formatear eventos para JSON
$eventos_json = json_encode($eventos_filtrados);

// Registrar actividad
registrarActividad($conexion, $usuario_id, "Acceso", "calendario", null, "Accedió al calendario");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario - OurCenter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        .fc-event {
            cursor: pointer;
        }
        #calendar {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .event-details {
            margin-top: 10px;
        }
        .fc-event-title {
            white-space: normal !important;
            overflow: visible;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Contenido principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Calendario de Actividades</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-mes">Mes</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-semana">Semana</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-dia">Día</button>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item <?php echo ($categoria == 'todos') ? 'active' : ''; ?>" href="calendario.php?categoria=todos">Todos los eventos</a></li>
                                <li><a class="dropdown-item <?php echo ($categoria == 'cursos') ? 'active' : ''; ?>" href="calendario.php?categoria=cursos">Cursos</a></li>
                                <li><a class="dropdown-item <?php echo ($categoria == 'sesion') ? 'active' : ''; ?>" href="calendario.php?categoria=sesion">Sesiones</a></li>
                                <li><a class="dropdown-item <?php echo ($categoria == 'pagos') ? 'active' : ''; ?>" href="calendario.php?categoria=pagos">Pagos</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div id="calendar"></div>
                    </div>
                </div>
                
                <!-- Modal para detalles del evento -->
                <div class="modal fade" id="eventoModal" tabindex="-1" aria-labelledby="eventoModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="eventoModalLabel">Detalles del Evento</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="evento-detalles">
                                    <h4 id="evento-titulo"></h4>
                                    <p id="evento-descripcion"></p>
                                    <div id="evento-fecha-hora">
                                        <strong>Fecha y hora:</strong> <span id="evento-fecha"></span>
                                    </div>
                                    <div id="evento-ubicacion-container" class="mt-2">
                                        <strong>Ubicación:</strong> <span id="evento-ubicacion"></span>
                                    </div>
                                    <div id="evento-modalidad-container" class="mt-2">
                                        <strong>Modalidad:</strong> <span id="evento-modalidad"></span>
                                    </div>
                                    <div id="evento-horario-container" class="mt-2">
                                        <strong>Horario:</strong> <span id="evento-horario"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <a href="#" class="btn btn-primary" id="evento-url">Ver detalles</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales-all.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var eventoModal = new bootstrap.Modal(document.getElementById('eventoModal'));
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: <?php echo $eventos_json; ?>,
            eventClick: function(info) {
                // Llenar modal con información del evento
                document.getElementById('evento-titulo').textContent = info.event.title;
                document.getElementById('evento-descripcion').textContent = info.event.extendedProps.description || '';
                
                // Formatear fecha y hora
                var fechaInicio = new Date(info.event.start);
                var fechaFormateada = fechaInicio.toLocaleDateString('es-ES', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
                
                var horaFormateada = '';
                if (info.event.allDay !== true) {
                    horaFormateada = ' a las ' + fechaInicio.toLocaleTimeString('es-ES', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    
                    if (info.event.end) {
                        var fechaFin = new Date(info.event.end);
                        horaFormateada += ' hasta las ' + fechaFin.toLocaleTimeString('es-ES', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    }
                }
                
                document.getElementById('evento-fecha').textContent = fechaFormateada + horaFormateada;
                
                // Mostrar u ocultar elementos según el tipo de evento
                var ubicacionContainer = document.getElementById('evento-ubicacion-container');
                var modalidadContainer = document.getElementById('evento-modalidad-container');
                var horarioContainer = document.getElementById('evento-horario-container');
                
                if (info.event.extendedProps.extendedProps && info.event.extendedProps.extendedProps.ubicacion) {
                    ubicacionContainer.style.display = 'block';
                    document.getElementById('evento-ubicacion').textContent = info.event.extendedProps.extendedProps.ubicacion;
                } else {
                    ubicacionContainer.style.display = 'none';
                }
                
                if (info.event.extendedProps.extendedProps && info.event.extendedProps.extendedProps.modalidad) {
                    modalidadContainer.style.display = 'block';
                    document.getElementById('evento-modalidad').textContent = info.event.extendedProps.extendedProps.modalidad.charAt(0).toUpperCase() + 
                                                                              info.event.extendedProps.extendedProps.modalidad.slice(1);
                } else {
                    modalidadContainer.style.display = 'none';
                }
                
                if (info.event.extendedProps.extendedProps && info.event.extendedProps.extendedProps.horario) {
                    horarioContainer.style.display = 'block';
                    document.getElementById('evento-horario').textContent = info.event.extendedProps.extendedProps.horario;
                } else {
                    horarioContainer.style.display = 'none';
                }
                
                // Configurar URL
                var urlBtn = document.getElementById('evento-url');
                if (info.event.url) {
                    urlBtn.href = info.event.url;
                    urlBtn.style.display = 'block';
                } else {
                    urlBtn.style.display = 'none';
                }
                
                eventoModal.show();
                
                // Evitar navegación al hacer clic en el evento
                info.jsEvent.preventDefault();
            },
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false,
                hour12: false
            }
        });
        
        calendar.render();
        
        // Botones para cambiar la vista
        document.getElementById('btn-mes').addEventListener('click', function() {
            calendar.changeView('dayGridMonth');
        });
        
        document.getElementById('btn-semana').addEventListener('click', function() {
            calendar.changeView('timeGridWeek');
        });
        
        document.getElementById('btn-dia').addEventListener('click', function() {
            calendar.changeView('timeGridDay');
        });
    });
    </script>
</body>
</html>