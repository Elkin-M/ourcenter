<?php
require_once __DIR__ . '/../models/curso.php';

// Obtener todos los cursos desde la base de datos
$cursos = Curso::obtenerTodos();
?>

<section id="courses" class="courses">
    <div class="container">
        <h2 class="section-title">Nuestros Cursos</h2>
        <p class="section-subtitle">Ofrecemos programas diseñados para todas las edades y niveles, con metodologías probadas que garantizan resultados efectivos.</p>
        
        <div class="courses-grid">
            <?php foreach ($cursos as $curso): ?>
                <div class="course-card">
                    <img src="<?php echo htmlspecialchars($curso['imagen_url']); ?>" alt="<?php echo htmlspecialchars($curso['nombre']); ?>" class="course-img">
                    <div class="course-content">
                        <span class="course-badge"><?php echo htmlspecialchars($curso['etiqueta']); ?></span>
                        <h3 class="course-title"><?php echo htmlspecialchars($curso['nombre']); ?></h3>
                        <p><?php echo htmlspecialchars($curso['descripcion']); ?></p>
                        <div class="course-details">
                            <span><i class="far fa-clock"></i> <?php echo Curso::formatearDuracion($curso['duracion_horas']); ?></span>
                            <span><i class="fas fa-signal"></i> <?php echo Curso::formatearNivel($curso['nivel']); ?></span>
                        </div>
                        <div class="course-price">$<?php echo Curso::formatearPrecio($curso['precio']); ?> / mes</div>
                        <a href="#register" class="btn btn-outline" data-curso-id="<?php echo $curso['id']; ?>">Inscribirse</a>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($cursos)): ?>
                <div class="no-courses">
                    <p>No hay cursos disponibles actualmente. Por favor, vuelve a consultar más tarde o contáctanos para más información.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>