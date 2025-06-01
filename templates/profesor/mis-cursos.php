<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Cursos - Plataforma Educativa</title>
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2c3e50;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
            --light: #f5f6fa;
            --dark: #34495e;
            --text: #333333;
            --border: #dcdde1;
            --shadow: rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: var(--text);
        }

        .dashboard {
            display: grid;
            grid-template-columns: 240px 1fr;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            background-color: var(--secondary);
            color: white;
            padding: 1.5rem 0;
            transition: all 0.3s;
        }

        .logo {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1.5rem;
            font-size: 1.4rem;
            font-weight: 700;
            text-align: center;
        }

        .nav-item {
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .nav-item:hover, .nav-item.active {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-item i {
            margin-right: 0.75rem;
        }

        /* Main Content */
        .main-content {
            padding: 1.5rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        /* Filter Bar */
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            border-radius: 0.5rem;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.125rem 0.25rem var(--shadow);
        }

        .filters {
            display: flex;
            gap: 1rem;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-label {
            font-size: 0.875rem;
            font-weight: 600;
        }

        .filter-select {
            padding: 0.375rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 0.25rem;
            background-color: var(--light);
            font-size: 0.875rem;
        }

        .search-box {
            display: flex;
            align-items: center;
            position: relative;
        }

        .search-input {
            padding: 0.375rem 0.75rem;
            padding-left: 2rem;
            border: 1px solid var(--border);
            border-radius: 0.25rem;
            font-size: 0.875rem;
            width: 240px;
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            color: #777;
        }

        /* Courses List */
        .courses-list {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 0.125rem 0.25rem var(--shadow);
        }

        .courses-table {
            width: 100%;
            border-collapse: collapse;
        }

        .courses-table th,
        .courses-table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .courses-table th {
            font-weight: 600;
            color: #777;
            font-size: 0.875rem;
        }

        .courses-table tr:last-child td {
            border-bottom: none;
        }

        .course-name {
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .course-code {
            font-size: 0.75rem;
            color: #777;
        }

        .course-icon {
            width: 32px;
            height: 32px;
            border-radius: 0.25rem;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-active {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success);
        }

        .status-upcoming {
            background-color: rgba(52, 152, 219, 0.2);
            color: var(--primary);
        }

        .status-completed {
            background-color: rgba(149, 165, 166, 0.2);
            color: #7f8c8d;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .course-students {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .student-count {
            font-weight: 600;
        }

        /* Course Detail Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            display: none;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background-color: white;
            border-radius: 0.5rem;
            width: 800px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 0.25rem 0.5rem var(--shadow);
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .modal-close {
            cursor: pointer;
            font-size: 1.5rem;
            color: #777;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-tabs {
            display: flex;
            border-bottom: 1px solid var(--border);
            margin-bottom: 1.5rem;
        }

        .modal-tab {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            font-weight: 600;
            color: #777;
            position: relative;
        }

        .modal-tab.active {
            color: var(--primary);
        }

        .modal-tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--primary);
        }

        .course-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .info-group {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        .info-label {
            font-size: 0.75rem;
            color: #777;
        }

        .info-value {
            font-weight: 600;
        }

        .course-description {
            margin-bottom: 1.5rem;
        }

        .course-description h4 {
            margin-bottom: 0.75rem;
        }

        .course-description p {
            line-height: 1.5;
            color: #555;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: flex-end;
            margin-top: 1.5rem;
            gap: 0.25rem;
        }

        .page-item {
            padding: 0.375rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 0.25rem;
            cursor: pointer;
            font-size: 0.875rem;
        }

        .page-item.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
            
            .filter-bar {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .filters {
                flex-wrap: wrap;
            }
            
            .courses-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <?php 
            $pageTitle = "Mis Cursos";
            include 'includes/header.php'; 
            ?>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="filters">
                    <div class="filter-group">
                        <span class="filter-label">Estado:</span>
                        <select class="filter-select">
                            <option>Activos</option>
                            <option>Próximos</option>
                            <option>Completados</option>
                            <option>Todos</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <span class="filter-label">Periodo:</span>
                        <select class="filter-select">
                            <option>Actual</option>
                            <option>2025-1</option>
                            <option>2024-2</option>
                            <option>2024-1</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <span class="filter-label">Ordenar por:</span>
                        <select class="filter-select">
                            <option>Nombre</option>
                            <option>Fecha</option>
                            <option>Estudiantes</option>
                        </select>
                    </div>
                </div>
                <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Buscar curso...">
                </div>
            </div>

            <!-- Courses List -->
            <div class="courses-list">
                <table class="courses-table">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Horario</th>
                            <th>Estudiantes</th>
                            <th>Próxima Clase</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="course-name">
                                    <div class="course-icon">PA</div>
                                    <div>
                                        <div>Programación Avanzada</div>
                                        <div class="course-code">PROG-2025-1</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>Lunes y Viernes</div>
                                <div class="course-code">10:00 - 12:00</div>
                            </td>
                            <td>
                                <div class="course-students">
                                    <span class="student-count">25</span>
                                    <span>estudiantes</span>
                                </div>
                            </td>
                            <td>
                                <div>16/05/2025</div>
                                <div class="course-code">10:00</div>
                            </td>
                            <td>
                                <span class="status-badge status-active">Activo</span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-primary">Ver Detalles</button>
                                    <button class="btn btn-outline">Tomar Asistencia</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="course-name">
                                    <div class="course-icon" style="background-color: #e74c3c;">DW</div>
                                    <div>
                                        <div>Diseño Web Responsive</div>
                                        <div class="course-code">DWR-2025-1</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>Viernes</div>
                                <div class="course-code">14:00 - 17:00</div>
                            </td>
                            <td>
                                <div class="course-students">
                                    <span class="student-count">18</span>
                                    <span>estudiantes</span>
                                </div>
                            </td>
                            <td>
                                <div>16/05/2025</div>
                                <div class="course-code">14:00</div>
                            </td>
                            <td>
                                <span class="status-badge status-active">Activo</span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-primary">Ver Detalles</button>
                                    <button class="btn btn-outline">Tomar Asistencia</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="course-name">
                                    <div class="course-icon" style="background-color: #f39c12;">BD</div>
                                    <div>
                                        <div>Base de Datos SQL</div>
                                        <div class="course-code">BDS-2025-1</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>Lunes</div>
                                <div class="course-code">09:00 - 12:00</div>
                            </td>
                            <td>
                                <div class="course-students">
                                    <span class="student-count">22</span>
                                    <span>estudiantes</span>
                                </div>
                            </td>
                            <td>
                                <div>19/05/2025</div>
                                <div class="course-code">09:00</div>
                            </td>
                            <td>
                                <span class="status-badge status-active">Activo</span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-primary">Ver Detalles</button>
                                    <button class="btn btn-outline">Tomar Asistencia</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="course-name">
                                    <div class="course-icon" style="background-color: #2ecc71;">SO</div>
                                    <div>
                                        <div>Sistemas Operativos</div>
                                        <div class="course-code">SOP-2025-1</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>Martes</div>
                                <div class="course-code">11:00 - 14:00</div>
                            </td>
                            <td>
                                <div class="course-students">
                                    <span class="student-count">16</span>
                                    <span>estudiantes</span>
                                </div>
                            </td>
                            <td>
                                <div>20/05/2025</div>
                                <div class="course-code">11:00</div>
                            </td>
                            <td>
                                <span class="status-badge status-active">Activo</span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-primary">Ver Detalles</button>
                                    <button class="btn btn-outline">Tomar Asistencia</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="course-name">
                                    <div class="course-icon" style="background-color: #9b59b6;">IA</div>
                                    <div>
                                        <div>Inteligencia Artificial</div>
                                        <div class="course-code">IA-2025-2</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>Jueves</div>
                                <div class="course-code">15:00 - 18:00</div>
                            </td>
                            <td>
                                <div class="course-students">
                                    <span class="student-count">0</span>
                                    <span>estudiantes</span>
                                </div>
                            </td>
                            <td>
                                <div>12/08/2025</div>
                                <div class="course-code">15:00</div>
                            </td>
                            <td>
                                <span class="status-badge status-upcoming">Próximo</span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-primary">Ver Detalles</button>
                                    <button class="btn btn-outline" disabled>Tomar Asistencia</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="pagination">
                    <span class="page-item">«</span>
                    <span class="page-item active">1</span>
                    <span class="page-item">2</span>
                    <span class="page-item">»</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Detail Modal (Hidden by default) -->
    <div class="modal" id="courseModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Programación Avanzada</div>
                <div class="modal-close">×</div>
            </div>
            <div class="modal-body">
                <div class="modal-tabs">
                    <div class="modal-tab active">Información</div>
                    <div class="modal-tab">Estudiantes</div>
                    <div class="modal-tab">Sesiones</div>
                    <div class="modal-tab">Anuncios</div>
                </div>

                <div class="course-info-grid">
                    <div class="info-group">
                        <div class="info-label">Código del Curso</div>
                        <div class="info-value">PROG-2025-1</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Estado</div>
                        <div class="info-value"><span class="status-badge status-active">Activo</span></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Horario</div>
                        <div class="info-value">Lunes y Viernes, 10:00 - 12:00</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Ubicación</div>
                        <div class="info-value">Aula 302</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Fecha de Inicio</div>
                        <div class="info-value">15/03/2025</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Fecha de Finalización</div>
                        <div class="info-value">20/07/2025</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Estudiantes Inscritos</div>
                        <div class="info-value">25 / 30</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Asistencia Promedio</div>
                        <div class="info-value">90%</div>
                    </div>
                </div>

                <div class="course-description">
                    <h4>Descripción del Curso</h4>
                    <p>Curso avanzado de programación orientado a profundizar en técnicas de desarrollo de software, patrones de diseño y optimización de código. Los estudiantes aprenderán a crear aplicaciones complejas y escalables utilizando las mejores prácticas de la industria.</p>
                </div>

                <div class="action-buttons">
                    <button class="btn btn-primary">Tomar Asistencia</button>
                    <button class="btn btn-primary">Crear Anuncio</button>
                    <button class="btn btn-outline">Ver Lista de Estudiantes</button>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
