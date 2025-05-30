<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');
require_once '../../../config/db.php';

// Verificar que se recibió el ID del curso
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>ID de curso no válido.</div>';
    exit;
}

$curso_id = (int)$_GET['id'];

try {
    // Obtener detalles completos del curso
    $stmt = $pdo->prepare("
        SELECT c.*, 
               COUNT(DISTINCT i.id) AS num_inscritos,
               COUNT(DISTINCT m.id) AS num_modulos,
               AVG(i.progreso_porcentaje) AS promedio_progreso
        FROM cursos c
        LEFT JOIN inscripciones i ON c.id = i.curso_id
        LEFT JOIN modulos m ON c.id = m.curso_id
        WHERE c.id = ?
        GROUP BY c.id
    ");
    $stmt->execute([$curso_id]);
    $curso = $stmt->fetch();

    if (!$curso) {
        echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Curso no encontrado.</div>';
        exit;
    }

    // Obtener lista de estudiantes inscritos
    $stmt = $pdo->prepare("
        SELECT u.nombre, u.email, i.fecha_creacion AS fecha_inscripcion, i.estado, i.progreso_porcentaje AS progreso
        FROM inscripciones i
        JOIN usuarios u ON i.usuario_id = u.id
        WHERE i.curso_id = ?
        ORDER BY i.fecha_creacion DESC
        LIMIT 5
    ");
    $stmt->execute([$curso_id]);
    $estudiantes = $stmt->fetchAll();

    // Obtener módulos del curso
    $stmt = $pdo->prepare("
        SELECT titulo, descripcion, orden, duracion_estimada
        FROM modulos
        WHERE curso_id = ?
        ORDER BY orden ASC
        LIMIT 5
    ");
    $stmt->execute([$curso_id]);
    $modulos = $stmt->fetchAll();

} catch (Exception $e) {
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error al obtener los detalles del curso: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit;
}

?>

<div class="row">
    <!-- Información principal del curso -->
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información General</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary font-weight-bold"><?= htmlspecialchars($curso['nombre']) ?></h6>
                        <p class="text-muted mb-3"><?= htmlspecialchars($curso['descripcion']) ?></p>
                        
                        <div class="detail-item mb-2">
                            <strong><i class="fas fa-calendar-alt text-muted me-2"></i>Fecha de inicio:</strong>
                            <span class="text-muted">
                                <?= !empty($curso['fecha_inicio']) ? date('d/m/Y', strtotime($curso['fecha_inicio'])) : 'No definida' ?>
                            </span>
                        </div>
                        
                        <div class="detail-item mb-2">
                            <strong><i class="fas fa-calendar-check text-muted me-2"></i>Fecha de fin:</strong>
                            <span class="text-muted">
                                <?= !empty($curso['fecha_fin']) ? date('d/m/Y', strtotime($curso['fecha_fin'])) : 'No definida' ?>
                            </span>
                        </div>
                        
                        <div class="detail-item mb-2">
                            <strong><i class="fas fa-clock text-muted me-2"></i>Duración:</strong>
                            <span class="text-muted"><?= $curso['duracion_horas'] ?? 'No definida' ?> horas</span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="detail-item mb-2">
                            <strong><i class="fas fa-dollar-sign text-muted me-2"></i>Precio:  </strong>
                            <span class="text-success font-weight-bold">$<?= number_format($curso['precio'] ?? 0, 2) ?></span>
                        </div>
                        
                        <div class="detail-item mb-2">
                            <strong><i class="fas fa-users text-muted me-2"></i>Cupo máximo:   </strong>
                            <span class="text-muted"> <?= $curso['cupo_maximo'] ?? 'Ilimitado' ?></span>
                        </div>
                        
                        <div class="detail-item mb-2">
                            <strong><i class="fas fa-signal text-muted me-2"></i>Nivel: </strong>
                            <span class="badge bg-info"> <?= ucfirst($curso['nivel'] ?? 'No definido') ?></span>
                        </div>
                        
                        <div class="detail-item mb-2">
                            <strong><i class="fas fa-check-circle text-muted me-2"></i>Estado: </strong>
                            <?php if (strtolower($curso['estado']) == 'activo'): ?>
                                <span class="badge bg-success"> Activo</span>
                            <?php elseif (strtolower($curso['estado']) == 'inactivo'): ?>
                                <span class="badge bg-danger"> Inactivo</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Pendiente</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($curso['requisitos'])): ?>
                    <div class="mt-3">
                        <strong><i class="fas fa-list-ul text-muted me-2"></i>Requisitos:</strong>
                        <p class="text-muted mt-2"><?= htmlspecialchars($curso['requisitos']) ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($curso['objetivos'])): ?>
                    <div class="mt-3">
                        <strong><i class="fas fa-target text-muted me-2"></i>Objetivos:</strong>
                        <p class="text-muted mt-2"><?= htmlspecialchars($curso['objetivos']) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Módulos del curso -->
        <?php if (!empty($modulos)): ?>
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-book me-2"></i>Módulos del Curso</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php foreach ($modulos as $index => $modulo): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">
                                        <span class="badge bg-primary rounded-pill me-2"><?= $index + 1 ?></span>
                                        <?= htmlspecialchars($modulo['titulo']) ?>
                                    </div>
                                    <p class="mb-1 text-muted"><?= htmlspecialchars($modulo['descripcion']) ?></p>
                                </div>
                                <?php if (!empty($modulo['duracion_estimada'])): ?>
                                    <span class="badge bg-secondary rounded-pill">
                                        <?= $modulo['duracion_estimada'] ?>h
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if ($curso['num_modulos'] > 5): ?>
                            <div class="list-group-item text-center text-muted">
                                <i class="fas fa-ellipsis-h me-2"></i>
                                Y <?= $curso['num_modulos'] - 5 ?> módulos más...
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Panel lateral con estadísticas -->
    <div class="col-md-4">
        <!-- Estadísticas del curso -->
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Estadísticas</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border-bottom pb-2">
                            <h4 class="text-primary mb-0"><?= $curso['num_inscritos'] ?></h4>
                            <small class="text-muted">Estudiantes</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="border-bottom pb-2">
                            <h4 class="text-info mb-0"><?= $curso['num_modulos'] ?></h4>
                            <small class="text-muted">Módulos</small>
                        </div>
                    </div>
                </div>
                
                <?php if ($curso['num_inscritos'] > 0): ?>
                    <div class="mt-3">
                        <label class="form-label">Progreso promedio</label>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: <?= min(($curso['promedio_progreso'] / 5) * 100, 100) ?>%">
                                <?= round(($curso['promedio_progreso'] / 5) * 100) ?>%
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($curso['cupo_maximo']) && $curso['cupo_maximo'] > 0): ?>
                    <div class="mt-3">
                        <label class="form-label">Ocupación del curso</label>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-warning" role="progressbar" 
                                 style="width: <?= min(($curso['num_inscritos'] / $curso['cupo_maximo']) * 100, 100) ?>%">
                                <?= round(($curso['num_inscritos'] / $curso['cupo_maximo']) * 100) ?>%
                            </div>
                        </div>
                        <small class="text-muted">
                            <?= $curso['num_inscritos'] ?> de <?= $curso['cupo_maximo'] ?> lugares ocupados
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Estudiantes recientes -->
        <?php if (!empty($estudiantes)): ?>
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Estudiantes Recientes</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php foreach ($estudiantes as $estudiante): ?>
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div style="overflow: auto;">
                                        <h6 class="mb-1"><?= htmlspecialchars($estudiante['nombre']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($estudiante['email']) ?></small>
                                        <br>
                                        <small class="text-muted">
                                            Inscrito: <?= date('d/m/Y', strtotime($estudiante['fecha_inscripcion'])) ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <?php if ($estudiante['estado'] == 'activo'): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php elseif ($estudiante['estado'] == 'completado'): ?>
                                            <span class="badge bg-primary">Completado</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($estudiante['progreso'])): ?>
                                            <br><small class="text-muted"><?= $estudiante['progreso'] ?>% progreso</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if ($curso['num_inscritos'] > 5): ?>
                            <div class="list-group-item px-0 py-2 text-center">
                                <a href="inscritos.php?curso_id=<?= $curso['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>Ver todos los estudiantes
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>