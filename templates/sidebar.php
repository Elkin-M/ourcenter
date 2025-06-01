   <!DOCTYPE html>
   <html lang="en">
   <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
     <!-- Favicon básico -->
     <link rel="icon" href="../images/favicon/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" sizes="180x180" href="../images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../images/favicon/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="../images/favicon/android-chrome-512x512.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../images/favicon/favicon-16x16.png">
    <link rel="manifest" href="../images/favicon/site.webmanifest">
    <meta name="theme-color" content="#0a1b5c">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/mstile-144x144.png">
    
    <title>Document</title>
   </head>
   <body>
    
   </body>
   </html>
   <!-- Preloader (mantenido del original) -->
   <style>
        :root {
            --primary-color: #0a1b5c;
            --primary-color-light: #122c8e;
            --secondary-color: #122c8e;
            --accent-color: #e74c3c;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
            --dark-bg: #343a40;
            --border-color: #dee2e6;
            --text-muted: #6c757d;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            background-color: var(--primary-color);
            color: white;
            height: 100vh;
            position: fixed;
            width: 250px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: all 0.7s;
        }

        .sidebar-collapsed {
            width: 70px;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 0;
            padding: 0.75rem 1rem;
            margin: 0.2rem 0;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }

        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white;
            border-left: 4px solid var(--accent-color);
        }
        
        .sidebar .nav-link i {
            width: 25px;
            text-align: center;
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        .sidebar-toggle {
            position: fixed;
            bottom: 20px;
            left: 10px;
            width: 40px;
            height: 40px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1001;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* Main content */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }

        .main-content-expanded {
            margin-left: 70px;
        }

        /* Header */
        .dashboard-header {
            background-color: white;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: white;
            margin: 0;
        }

        /* Logo */
        .logo-container {
            padding: 1.5rem 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            width: 40px;
            height: auto;
            margin-right: 10px;
        }

        .logo-text {
            font-weight: 600;
            font-size: 1.2rem;
            white-space: nowrap;
        }

        .logo-short {
            display: none;
        }

        /* Cards */
        .card {
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-radius: 8px;
            border: none;
            margin-bottom: 1.5rem;
            transition: all 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .card-text {
            font-weight: 700;
            margin-bottom: 0;
        }

        .bg-primary-color {
            background-color: var(--primary-color) !important;
        }

        .bg-secondary-color {
            background-color: var(--secondary-color) !important;
        }

        /* Quick actions */
        .quick-actions {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .action-btn {
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--primary-color);
            background-color: rgba(10, 27, 92, 0.05);
        }

        .action-btn:hover {
            background-color: rgba(10, 27, 92, 0.1);
            transform: translateY(-3px);
        }

        .action-btn i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        /* Recent items */
        .recent-items {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .table th {
            font-weight: 600;
            color: var(--text-muted);
        }

        .table td {
            vertical-align: middle;
        }

        .badge {
            padding: 0.5em 0.75em;
            font-weight: 500;
        }

        .card-header {
            font-weight: 600;
            padding: 1rem 1.25rem;
            background-color: white;
            border-bottom: 1px solid var(--border-color);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--dark-bg);
        }

        /* Canvas container */
        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 1rem;
        }

        /* User dropdown */
        .user-dropdown img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .user-dropdown .dropdown-toggle {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            color: var(--dark-bg);
        }

        .user-dropdown .dropdown-toggle::after {
            display: none;
        }

        .user-dropdown .dropdown-menu {
            width: 220px;
            padding: 0.5rem 0;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .user-dropdown .dropdown-item {
            padding: 0.6rem 1.5rem;
        }

        .user-dropdown .dropdown-item i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
        }

        .user-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .user-name {
            font-weight: 600;
        }

        .user-role {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        /* Notificaciones */
        .notifications {
            position: relative;
        }

        .notifications-icon {
            font-size: 1.25rem;
            color: var(--text-muted);
            cursor: pointer;
        }

        .notifications-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--accent-color);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .alert-box {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            width: 300px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
            background-color: white;
            transition: all 0.3s;
            transform: translateX(350px);
            opacity: 0;
        }

        .alert-box.show {
            transform: translateX(0);
            opacity: 1;
        }

        .alert-header {
            background-color: var(--primary-color);
            color: white;
            padding: 0.75rem 1rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
        }

        .alert-body {
            padding: 1rem;
        }

        .alert-message {
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .alert-message:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .status-new {
            background-color: var(--accent-color);
        }

        .status-process {
            background-color: var(--warning-color);
        }

        .status-completed {
            background-color: var(--success-color);
        }

        .status-cancelled {
            background-color: var(--danger-color);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 60px;
            }
            
            .sidebar.expanded {
                width: 250px;
            }
            
            .main-content {
                margin-left: 70px;
            }
            .logo img{
                margin-right: 0px;
            }
            .logo-text, .nav-text {
                display: none;
            }
            
            .logo-short {
                display: block;
            }
            
            .sidebar.expanded .logo-text, 
            .sidebar.expanded .nav-text {
                display: inline;
            }
            
            .sidebar.expanded .logo-short {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .sidebar {
                transform: translateX(-100%);
                width: 250px;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .logo-text, .nav-text {
                display: inline;
            }
            
            .logo-short {
                display: none;
            }
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .actions-container {
                margin-top: 1rem;
                width: 100%;
            }
        }
    </style>
    
        
    <!-- Sidebar Toggle Button -->
    <div class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Sidebar Navigation -->
    <div class="sidebar" id="sidebar">
        <div class="logo-container">
            <div class="logo">
                <img src="../../images/logo.webp" alt="Logo de Our Center">
                <div class="logo-text" style="
    margin-left: 10px;">OUR CENTER</div>
                <!-- <div class="logo-short">OC</div> -->
            </div>
        </div>
        
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./admin/Usuarios.php">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Usuarios</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./admin/cursos.php">
                    <i class="fas fa-book"></i>
                    <span class="nav-text">Cursos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./admin/inscripciones.php">
                    <i class="fas fa-user-graduate"></i>
                    <span class="nav-text">Inscripciones</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./admin/pagos.php">
                    <i class="fas fa-credit-card"></i>
                    <span class="nav-text">Pagos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./admin/solicitudes.php">
                    <i class="fas fa-envelope"></i>
                    <span class="nav-text">Solicitudes</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./admin/reportes.php">
                    <i class="fas fa-chart-bar"></i>
                    <span class="nav-text">Reportes</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./admin/configuracion.php">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">Configuración</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Bootstrap JS y Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
   
    <!-- Scripts personalizados -->
    <script>
 // Elementos del DOM
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
const sidebarToggle = document.getElementById('sidebarToggle');
const body = document.body;

// Crear overlay para dispositivos móviles
const sidebarOverlay = document.createElement('div');
sidebarOverlay.className = 'sidebar-overlay';
document.body.appendChild(sidebarOverlay);

// Función para cambiar el estado del sidebar
function toggleSidebar() {
    sidebar.classList.toggle('show');
    body.classList.toggle('sidebar-hidden');
    
    // En dispositivos móviles, mostrar/ocultar overlay
    if (window.innerWidth < 768) {
        sidebarOverlay.classList.toggle('active');
    }
}

// Event listeners
sidebarToggle.addEventListener('click', toggleSidebar);

// Cerrar sidebar al hacer clic en el overlay (en móvil)
sidebarOverlay.addEventListener('click', function() {
    if (sidebar.classList.contains('show') && window.innerWidth < 768) {
        toggleSidebar();
    }
});

// Función para ajustar la interfaz según el tamaño de la pantalla
function adjustForScreenSize() {
    if (window.innerWidth < 768) {
        sidebar.classList.remove('show');
        body.classList.add('sidebar-hidden');
        mainContent.style.marginLeft = '0';
    } else {
        // En pantallas grandes, mostrar sidebar por defecto
        // a menos que el usuario haya elegido ocultarlo previamente
        if (!body.classList.contains('sidebar-hidden-by-user')) {
            sidebar.classList.add('show');
            body.classList.remove('sidebar-hidden');
        }
    }
}

// Guardar preferencia del usuario al ocultar sidebar manualmente en pantallas grandes
sidebarToggle.addEventListener('click', function() {
    if (window.innerWidth >= 768) {
        if (sidebar.classList.contains('show')) {
            body.classList.remove('sidebar-hidden-by-user');
        } else {
            body.classList.add('sidebar-hidden-by-user');
        }
    }
});

// Verificar el tamaño de la pantalla al cargar y redimensionar
window.addEventListener('load', adjustForScreenSize);
window.addEventListener('resize', adjustForScreenSize);

// Notificaciones
 const notificationsBtn = document.getElementById('notificationsBtn');
        const alertBox = document.getElementById('alertBox');
        const closeAlertBox = document.getElementById('closeAlertBox');
        
        notificationsBtn.addEventListener('click', function() {
            alertBox.classList.toggle('show');
        });
        
        closeAlertBox.addEventListener('click', function() {
            alertBox.classList.remove('show');
        });
        
        // Detectar clic fuera del alertBox para cerrarlo
        document.addEventListener('click', function(event) {
            if (!alertBox.contains(event.target) && event.target !== notificationsBtn) {
                alertBox.classList.remove('show');
            }
        });

        // Cerrar el alertBox al hacer clic en el botón de cerrar
        closeAlertBox.addEventListener('click', function() {
            alertBox.classList.remove('show');
        });
        // Cerrar el alertBox al hacer clic fuera de él
        document.addEventListener('click', function(event) {
            if (!alertBox.contains(event.target) && event.target !== notificationsBtn) {
                alertBox.classList.remove('show');
            }
        });
        
  </script>