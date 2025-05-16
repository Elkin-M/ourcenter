<?php
// Inicializar la conexión a la base de datos
require_once __DIR__ . '/config/conexion-courses.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Center</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Iconos favicon -->

    <!-- Favicon básico -->
    <link rel="icon" href="./images/favicon/favicon.ico" sizes="any">

    <!-- Favicons para iOS/Safari -->
    <link rel="apple-touch-icon" sizes="180x180" href="./images/favicon/apple-touch-icon.png">

    <!-- Favicons para dispositivos Android -->
    <link rel="icon" type="image/png" sizes="192x192" href="./images/favicon/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="./images/favicon/android-chrome-512x512.png">

    <!-- Favicons standard -->
    <link rel="icon" type="image/png" sizes="32x32" href="./images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./images/favicon/favicon-16x16.png">

    <!-- Manifiesto para PWA (Progressive Web App) -->
    <link rel="manifest" href="./images/favicon/site.webmanifest">

    <!-- Meta tag para controlar el color de la barra de dirección en móviles -->
    <meta name="theme-color" content="#0a1b5c">

    <!-- Para Microsoft Edge y IE -->
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/mstile-144x144.png">
</head>
<body>
    <header id="header">
        <div class="container nav-container">
            <div class="logo">
                <img src="./images/logo.webp" alt="Logo de Corporación Our Center">
                <div class="logo-text">CORPORACIÓN OUR CENTER</div>
                <div class="logo-short">C. OURCENTER</div>
            </div>
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <nav>
                <ul id="navMenu">
                    <li><a href="#home" class="active">Inicio</a></li>
                    <li><a href="#about">Sobre Nosotros</a></li>
                    <li><a href="#courses">Cursos</a></li>
                    <li><a href="#register">Inscripción</a></li>
                    <li><a href="#location">Ubicación</a></li>
                    <li><a href="#contact">Contacto</a></li>
                </ul>
            </nav>
            
        </div>
    </header>

    <main>
        <section id="home" class="hero">
            <div class="carousel" id="heroCarousel">
                <!-- Slide 1 -->
                <div class="carousel-slide active" style="background-image: url('./images/carrusel\(1\).webp')">
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <h1>Enseñamos inglés como una habilidad para el futuro</h1>
                        <p>En Corporación Our Center, te preparamos para un mundo globalizado con métodos modernos y efectivos de aprendizaje del idioma inglés.</p>
                        <a href="./sesion.php" class="btn">Inscríbete Ahora</a>
                    </div>
                </div>
                
                <!-- Slide 2 -->
                <div class="carousel-slide" style="background-image: url('./images/carrusel\(2\).jpeg')">
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <h1>Profesores certificados internacionalmente</h1>
                        <p>Nuestro equipo docente cuenta con amplia experiencia y certificaciones que garantizan una enseñanza de calidad y efectiva.</p>
                        <a href="#about" class="btn">Conoce Nuestro Equipo</a>
                    </div>
                </div>
                
                <!-- Slide 3 -->
                <div class="carousel-slide" style="background-image: url('./images/carrusel\(3\).jpeg')">
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <h1>Cursos para todas las edades y niveles</h1>
                        <p>Desde niños hasta adultos, contamos con programas diseñados específicamente para cada etapa de aprendizaje y objetivo.</p>
                        <a href="#courses" class="btn">Explora Nuestros Cursos</a>
                    </div>
                </div>
            </div>
            
            <!-- Carousel Navigation -->
            <div class="carousel-arrows">
                <div class="carousel-arrow prev-slide">
                    <i class="fas fa-chevron-left"></i>
                </div>
                <div class="carousel-arrow next-slide">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
            
            <div class="carousel-buttons" id="carouselDots">
                <div class="carousel-dot active" data-slide="0"></div>
                <div class="carousel-dot" data-slide="1"></div>
                <div class="carousel-dot" data-slide="2"></div>
            </div>
            
            <div class="scroll-indicator" id="scrollDown">
                <i class="fas fa-chevron-down"></i>
            </div>
        </section>

        <section id="about" class="about">
            <div class="container">
                <h2 class="section-title">Sobre Nosotros</h2>
                <p class="section-subtitle">Somos una institución comprometida con la excelencia en la enseñanza del idioma inglés, formando estudiantes preparados para enfrentar los desafíos del futuro.</p>
                
                <div class="about-grid">
                    <div class="about-card">
                        <i class="fas fa-history"></i>
                        <h3>Nuestra Historia</h3>
                        <p>Fundada con el propósito de transformar la educación del idioma inglés, Corporación Our Center nació de la visión de crear un espacio donde el aprendizaje fuera efectivo, dinámico y orientado al futuro.</p>
                    </div>
                    <div class="about-card">
                        <i class="fas fa-lightbulb"></i>
                        <h3>Metodología</h3>
                        <p>Utilizamos un enfoque comunicativo y práctico, centrado en desarrollar las cuatro habilidades fundamentales: hablar, escuchar, leer y escribir, con énfasis en situaciones reales.</p>
                    </div>
                    <div class="about-card">
                        <i class="fas fa-users"></i>
                        <h3>Nuestro Equipo</h3>
                        <p>Contamos con docentes altamente calificados y con experiencia internacional, apasionados por la enseñanza y comprometidos con el éxito de nuestros estudiantes.</p>
                    </div>
                </div>
            </div>
        </section>

         <!-- Incluimos la sección de cursos dinámica -->
        <?php include_once __DIR__ . '/sections/courses.php'; ?>

        <section id="register" class="form-section">
            <div class="container">
                <h2 class="section-title">Formulario de Inscripción</h2>
                <p class="section-subtitle">Completa el siguiente formulario para iniciar tu proceso de inscripción. Nos pondremos en contacto contigo a la brevedad.</p>
                
                <div class="form-container">
                    <form id="registrationForm">
                        <div class="form-group">
                            <label for="fullName" class="form-label">Nombre completo</label>
                            <input type="text" id="fullName" name="fullName" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">Teléfono / WhatsApp</label>
                            <input type="tel" id="phone" name="phone" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="age" class="form-label">Edad</label>
                            <input type="number" id="age" name="age" class="form-control" min="5" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="course" class="form-label">Curso de interés</label>
                            <select id="course" name="course" class="form-control" required>
                                <option value="">Seleccionar curso</option>
                                <?php 
                                // Obtener cursos de nuevo para el formulario
                                $cursoOptions = Curso::obtenerTodos();
                                foreach ($cursoOptions as $curso) {
                                    echo '<option value="' . $curso['id'] . '">' . htmlspecialchars($curso['nombre']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="level" class="form-label">Nivel de inglés actual</label>
                            <select id="level" name="level" class="form-control" required>
                                <option value="">Seleccionar nivel</option>
                                <option value="beginner">Principiante</option>
                                <option value="elementary">Básico</option>
                                <option value="intermediate">Intermedio</option>
                                <option value="advanced">Avanzado</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message" class="form-label">Mensaje o consulta (opcional)</label>
                            <textarea id="message" name="message" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <button type="submit" class="btn">Enviar Inscripción</button>
                    </form>
                </div>
            </div>
        </section>

        <section id="location">
            <div class="container">
                <h2 class="section-title">Nuestra Ubicación</h2>
                <p class="section-subtitle">Visítanos en nuestras instalaciones modernas y acogedoras, diseñadas para crear un ambiente óptimo de aprendizaje.</p>
                
                <div class="location-container">
                    <div class="location-info">
                        <h3>¿Cómo llegar a Corporación Our Center?</h3>
                        <p>Estamos ubicados en una zona céntrica y de fácil acceso, con múltiples opciones de transporte público y estacionamiento cercano.</p>
                        
                        <div class="contact-info">
                            <h4>Información de Contacto</h4>
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>130014 Cra59c #Mz 71 Lt 3, Los Calamares, Cartagena de Indias, Bolívar</span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <span>+57 XXX-XXX-XXXX</span>
                            </div>
                            <div class="contact-item">
                                <i class="fab fa-whatsapp"></i>
                                <span>+57 XXX-XXX-XXXX</span>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <span>info@ourcenter.com</span>
                            </div>
                            <div class="contact-item">
                                <i class="far fa-clock"></i>
                                <span>Lun - Vie: 8:00 AM - 8:00 PM</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3812.635610228271!2d-75.49648219527954!3d10.393406290057856!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8ef625728a99fff1%3A0xdb9155c37a1a4a5d!2sCorporaci%C3%B3n%20Our%20Center!5e0!3m2!1ses-419!2sco!4v1746046305114!5m2!1ses-419!2sco" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <div class="footer-logo">
                        <img src="./images/logo.webp" alt="Logo de Corporación Our Center">
                        <div class="footer-logo-text">CORPORACIÓN OUR CENTER</div>
                    </div>
                    <p>Enseñamos inglés como una habilidad para el futuro, preparando a nuestros estudiantes para un mundo globalizado.</p>
                    <div class="social-links">
                        <a href="https://www.facebook.com/CORPOURCENTER" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/corporacionour/" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        <a href="https://api.whatsapp.com/send?phone=573003774804" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h4>Enlaces Rápidos</h4>
                    <ul>
                        <li><a href="#home">Inicio</a></li>
                        <li><a href="#about">Sobre Nosotros</a></li>
                        <li><a href="#courses">Cursos</a></li>
                        <li><a href="#register">Inscripción</a></li>
                        <li><a href="#location">Ubicación</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h4>Cursos</h4>
                    <ul>
                        <?php 
                        // Mostrar cursos en el footer dinámicamente
                        $footerCursos = Curso::obtenerTodos();
                        foreach ($footerCursos as $curso) {
                            echo '<li><a href="#courses">' . htmlspecialchars($curso['nombre']) . '</a></li>';
                        }
                        ?>
                        <li><a href="#register">Horarios de clase</a></li>
                        <li><a href="#register">Materiales de estudio</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h4>Contacto</h4>
                    <ul>
                        <li><a href="#location">Nuestra ubicación</a></li>
                        <li><a href="tel:+XXXXXXXXXXXX">+XX XXX-XXX-XXXX</a></li>
                        <li><a href="mailto:info@ourcenter.com">info@ourcenter.com</a></li>
                        <li><a href="#register">Solicitar información</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> Corporación Our Center. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
    <script>
        const menuToggle = document.getElementById('menuToggle');
        const navMenu = document.getElementById('navMenu');
        const menuLinks = navMenu.querySelectorAll('a'); // Seleccionamos todos los enlaces dentro de navMenu
    
        function toggleScroll() {
            if (navMenu.classList.contains('active')) {
                document.body.classList.add('no-scroll');
            } else {
                document.body.classList.remove('no-scroll');
            }
        }
    
        menuToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            menuToggle.innerHTML = navMenu.classList.contains('active') ? 
                '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
            toggleScroll(); // Llamada para bloquear/desbloquear el scroll
        });
    
        // Cerramos el menú y desbloqueamos el scroll cuando se hace clic en un enlace del menú
        menuLinks.forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                menuToggle.innerHTML = '<i class="fas fa-bars"></i>'; // Cambia el ícono del toggle
                toggleScroll(); // Llamada para desbloquear el scroll
            });
        });
    
        // Hero carousel functionality
        const carousel = document.getElementById('heroCarousel');
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.carousel-dot');
        const prevBtn = document.querySelector('.prev-slide');
        const nextBtn = document.querySelector('.next-slide');
        const scrollDown = document.getElementById('scrollDown');
    
        let currentSlide = 0;
        let autoSlideInterval;
    
        // Initialize the carousel
        function initCarousel() {
            showSlide(currentSlide);
            startAutoSlide();
    
            // Event listeners
            prevBtn.addEventListener('click', () => {
                prevSlide();
                resetAutoSlide();
            });
    
            nextBtn.addEventListener('click', () => {
                nextSlide();
                resetAutoSlide();
            });
    
            dots.forEach(dot => {
                dot.addEventListener('click', () => {
                    const slideIndex = parseInt(dot.getAttribute('data-slide'));
                    showSlide(slideIndex);
                    resetAutoSlide();
                });
            });
    
            scrollDown.addEventListener('click', () => {
                const aboutSection = document.querySelector('#about') || document.querySelector('div');
                window.scrollTo({
                    top: aboutSection.offsetTop - 70,
                    behavior: 'smooth'
                });
            });
        }
    
        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
    
            slides[index].classList.add('active');
            dots[index].classList.add('active');
            currentSlide = index;
        }
    
        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }
    
        function prevSlide() {
            currentSlide = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(currentSlide);
        }
    
        function startAutoSlide() {
            autoSlideInterval = setInterval(nextSlide, 6000);
        }
    
        function resetAutoSlide() {
            clearInterval(autoSlideInterval);
            startAutoSlide();
        }
    
        // Header behavior on scroll
        const header = document.getElementById('header');
        let lastScrollTop = 0;
    
        function handleScroll() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const heroHeight = document.querySelector('.hero').offsetHeight;
    
            // Make header transparent when at top of page
            if (scrollTop < 50) {
                header.classList.add('transparent');
                header.classList.remove('scrolled', 'hidden');
            } 
            // Hide header when scrolling down and show when scrolling up
            else if (scrollTop > lastScrollTop && scrollTop > heroHeight / 2) {
                header.classList.add('hidden');
                header.classList.remove('transparent');
            } else {
                header.classList.remove('hidden', 'transparent');
                header.classList.add('scrolled');
            }
    
            lastScrollTop = scrollTop;
        }
    
        // Initialize on document load
        document.addEventListener('DOMContentLoaded', () => {
            initCarousel();
    
            // Initial header state
            if (window.pageYOffset < 50) {
                header.classList.add('transparent');
            }
    
            // Add scroll event listener
            window.addEventListener('scroll', handleScroll);
        });
    </script>
    
    
</body>
</html>