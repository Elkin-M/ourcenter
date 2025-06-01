<?php
include($_SERVER['DOCUMENT_ROOT'] . '/ourcenter/config/init.php');

// editar_curso.php
require_once '../../../config/db.php';
$current_page = 'Cursos';

// Verificar si se proporciona ID del curso
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensaje'] = 'ID de curso no válido.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: cursos.php');
    exit;
}

$curso_id = (int)$_GET['id'];

// Obtener datos del curso
$stmt = $pdo->prepare("SELECT * FROM cursos WHERE id = ?");
$stmt->execute([$curso_id]);
$curso = $stmt->fetch();

if (!$curso) {
    $_SESSION['mensaje'] = 'Curso no encontrado.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: cursos.php');
    exit;
}

// Procesar formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $duracion_horas = (int)$_POST['duracion_horas'];
    $precio = (float)$_POST['precio'];
    $estado = $_POST['estado'];
    $instructor = trim($_POST['instructor']);
    $modalidad = $_POST['modalidad'];
    $categoria = trim($_POST['categoria']);
    $requisitos = trim($_POST['requisitos']);
    $objetivos = trim($_POST['objetivos']);
    
    $errors = [];
    
    // Validaciones
    if (empty($nombre)) {
        $errors[] = 'El nombre del curso es obligatorio.';
    }
    
    if (empty($descripcion)) {
        $errors[] = 'La descripción es obligatoria.';
    }
    
    if (empty($fecha_inicio)) {
        $errors[] = 'La fecha de inicio es obligatoria.';
    }
    
    if ($duracion_horas <= 0) {
        $errors[] = 'La duración debe ser mayor a 0 horas.';
    }
    
    if ($precio < 0) {
        $errors[] = 'El precio no puede ser negativo.';
    }
    
    // Procesar imagen si se sube una nueva
    $imagen_url = $curso['imagen_url']; // Mantener la imagen actual por defecto
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../images/cursos/';
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['imagen']['type'], $allowed_types)) {
            $errors[] = 'Tipo de archivo no permitido. Solo se permiten JPG, PNG, GIF y WebP.';
        }
        
        if ($_FILES['imagen']['size'] > $max_size) {
            $errors[] = 'El archivo es demasiado grande. Máximo 5MB.';
        }
        
        if (empty($errors)) {
            // Crear directorio si no existe
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $new_filename = 'curso_' . $curso_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $upload_path)) {
                // Eliminar imagen anterior si existe y no es la por defecto
                if (!empty($curso['imagen_url']) && $curso['imagen_url'] !== '/images/cursos/default-course.jpg') {
                    $old_image_path = '../../' . ltrim($curso['imagen_url'], '/');
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                $imagen_url = '/images/cursos/' . $new_filename;
            } else {
                $errors[] = 'Error al subir la imagen.';
            }
        }
    }
    
    // Si no hay errores, actualizar el curso
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE cursos SET 
                    nombre = ?, 
                    descripcion = ?, 
                    fecha_inicio = ?, 
                    duracion_horas = ?, 
                    precio = ?, 
                    estado = ?, 
                    instructor = ?, 
                    modalidad = ?, 
                    categoria = ?, 
                    requisitos = ?, 
                    objetivos = ?, 
                    imagen_url = ?,
                    fecha_actualizacion = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $nombre, $descripcion, $fecha_inicio, $duracion_horas, $precio, 
                $estado, $instructor, $modalidad, $categoria, $requisitos, 
                $objetivos, $imagen_url, $curso_id
            ]);
            
            $_SESSION['mensaje'] = 'Curso actualizado exitosamente.';
            $_SESSION['tipo_mensaje'] = 'success';
            header('Location: cursos.php');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = 'Error al actualizar el curso: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Curso - Our Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" href="/ourcenter/images/favicon/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" sizes="180x180" href="/ourcenter/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/ourcenter/images/favicon/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/ourcenter/images/favicon/android-chrome-512x512.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/ourcenter/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/ourcenter/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="/ourcenter/images/favicon/site.webmanifest">
    <meta name="theme-color" content="#0a1b5c">
    <link rel="stylesheet" href="/ourcenter/css/dashboard.css">
    
    <style>
        :root {
            --primary-color: #667eea;
            --primary-dark: #5a6fd8;
            --success-color: #48bb78;
            --success-dark: #38a169;
            --info-color: #4299e1;
            --warning-color: #ed8936;
            --danger-color: #f56565;
            --light-bg: #f7fafc;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e0;
            --gray-400: #a0aec0;
            --gray-500: #718096;
            --gray-600: #4a5568;
            --gray-700: #2d3748;
            --gray-800: #1a202c;
            --border-radius: 12px;
            --border-radius-lg: 16px;
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, var(--light-bg) 0%, var(--gray-100) 100%);
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: var(--gray-700);
        }

        .page-content {
            padding: 2rem;
            min-height: calc(100vh - 60px);
        }

        /* Breadcrumb mejorado */
        .breadcrumb {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 0.75rem 1.25rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 0;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            color: var(--gray-400);
        }
        
        .breadcrumb-item a {
            color: var(--gray-600);
            text-decoration: none;
            transition: var(--transition);
        }

        .breadcrumb-item a:hover {
            color: var(--primary-color);
        }
        
        .breadcrumb-item.active {
            color: var(--gray-700);
            font-weight: 500;
        }

        /* Header mejorado */
        .page-header {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-800);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-title i {
            color: var(--primary-color);
        }

        /* Cards mejoradas */
        .form-section {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .form-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--info-color));
        }

        .form-section:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .section-title {
            color: var(--gray-800);
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-100);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title i {
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        /* Formularios mejorados */
        .form-label {
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .required-field::after {
            content: " *";
            color: var(--danger-color);
            font-weight: bold;
        }

        .form-control,
        .form-select {
            border: 2px solid var(--gray-200);
            border-radius: var(--border-radius);
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: var(--transition);
            background-color: var(--white);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
            background-color: var(--white);
        }

        .form-control:hover,
        .form-select:hover {
            border-color: var(--gray-300);
        }

        .form-text {
            color: var(--gray-500);
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        /* Botones mejorados */
        .btn {
            font-weight: 600;
            border-radius: var(--border-radius);
            padding: 0.75rem 1.5rem;
            font-size: 0.95rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: 2px solid transparent;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border-color: var(--primary-color);
            color: white;
            box-shadow: var(--shadow-sm);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
            border-color: var(--primary-dark);
            box-shadow: var(--shadow);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: var(--gray-100);
            border-color: var(--gray-300);
            color: var(--gray-700);
        }

        .btn-secondary:hover {
            background-color: var(--gray-200);
            border-color: var(--gray-400);
            color: var(--gray-800);
            transform: translateY(-1px);
        }

        /* Manejo de imágenes mejorado */
        .image-upload-section {
            background: var(--gray-50);
            border: 2px dashed var(--gray-300);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            text-align: center;
            transition: var(--transition);
        }

        .image-upload-section:hover {
            border-color: var(--primary-color);
            background-color: rgba(102, 126, 234, 0.05);
        }

        .image-upload-section.dragover {
            border-color: var(--primary-color);
            background-color: rgba(102, 126, 234, 0.1);
        }

        .current-image-container {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }

        .preview-image {
            max-width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            border: 3px solid var(--white);
            transition: var(--transition);
        }

        .preview-image:hover {
            box-shadow: var(--shadow-lg);
            transform: scale(1.02);
        }

        .image-preview-container {
            position: relative;
            display: inline-block;
            margin: 0.5rem;
        }

        .remove-image {
            position: absolute;
            top: -10px;
            right: -10px;
            background: linear-gradient(135deg, var(--danger-color), #e53e3e);
            color: white;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .remove-image:hover {
            background: linear-gradient(135deg, #e53e3e, var(--danger-color));
            transform: scale(1.1);
        }

        /* Alertas mejoradas */
        .alert {
            border: none;
            border-radius: var(--border-radius-lg);
            padding: 1.25rem 1.5rem;
            box-shadow: var(--shadow);
            border-left: 4px solid;
        }

        .alert-danger {
            background-color: rgba(245, 101, 101, 0.1);
            border-left-color: var(--danger-color);
            color: #c53030;
        }

        .alert-success {
            background-color: rgba(72, 187, 120, 0.1);
            border-left-color: var(--success-color);
            color: #276749;
        }

        /* Espaciado mejorado */
        .mb-3 {
            margin-bottom: 1.5rem !important;
        }

        .mb-4 {
            margin-bottom: 2rem !important;
        }

        /* Grid mejorado */
        .row {
            margin-left: -1rem;
            margin-right: -1rem;
        }

        .row > * {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        /* Estados de validación */
        .is-invalid {
            border-color: var(--danger-color) !important;
            box-shadow: 0 0 0 0.2rem rgba(245, 101, 101, 0.15) !important;
        }

        .is-valid {
            border-color: var(--success-color) !important;
            box-shadow: 0 0 0 0.2rem rgba(72, 187, 120, 0.15) !important;
        }

        /* Responsive mejorado */
        @media (max-width: 768px) {
            .page-content {
                padding: 1rem;
            }

            .page-header {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .page-title {
                font-size: 1.5rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .form-section {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .section-title {
                font-size: 1.1rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
                text-align: left;
            }

            .btn {
                width: 100%;
                justify-content: center;
                margin-bottom: 0.75rem;
            }

            .d-flex.justify-content-between {
                flex-direction: column-reverse;
            }

            .preview-image {
                height: 150px;
            }

            .image-upload-section {
                padding: 1.5rem;
            }

            .current-image-container {
                padding: 1rem;
            }

            .breadcrumb {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }

            .row {
                margin-left: -0.5rem;
                margin-right: -0.5rem;
            }

            .row > * {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 576px) {
            .page-content {
                padding: 0.75rem;
            }

            .page-header,
            .form-section {
                padding: 1rem;
            }

            .page-title {
                font-size: 1.25rem;
            }

            .section-title {
                font-size: 1rem;
            }

            .btn {
                padding: 0.6rem 1.25rem;
                font-size: 0.9rem;
            }
        }

        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-section {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Loading states */
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .loading::after {
            content: '';
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 0.5rem;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <!-- Preloader -->
    <?php include '../../preloader.php';?>
    <?php include '../../header.php'; ?>

    <!-- Sidebar Toggle Button -->
    <div class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </div>

    <div class="container-fluid">
        <div class="page-content">
            <!-- Mostrar errores si existen -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-exclamation-triangle me-3 mt-1"></i>
                        <div class="flex-grow-1">
                            <strong>Error al procesar el formulario:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Encabezado mejorado -->
            <div class="page-header">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div class="flex-grow-1">
                        <h1 class="page-title">
                            <i class="fas fa-edit"></i>
                            <span>Editar Curso</span>
                        </h1>
                        <nav aria-label="breadcrumb" class="mt-3">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="../dashboard.php">
                                        <i class="fas fa-home me-1"></i>Dashboard
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="cursos.php">
                                        <i class="fas fa-graduation-cap me-1"></i>Cursos
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">Editar Curso</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="../cursos.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>Volver a Cursos
                    </a>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data" id="form-editar-curso">
                <!-- Información Básica -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        <span>Información Básica</span>
                    </h4>
                    
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="mb-3">
                                <label for="nombre" class="form-label required-field">Nombre del Curso</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?= htmlspecialchars($curso['nombre']) ?>" 
                                       placeholder="Ingrese el nombre del curso" required>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label for="estado" class="form-label required-field">Estado</label>
                                <select class="form-select" id="estado" name="estado" required>
                                    <option value="">Seleccionar estado</option>
                                    <option value="activo" <?= $curso['estado'] === 'activo' ? 'selected' : '' ?>>
                                        <i class="fas fa-check-circle"></i> Activo
                                    </option>
                                    <option value="inactivo" <?= $curso['estado'] === 'inactivo' ? 'selected' : '' ?>>
                                        <i class="fas fa-pause-circle"></i> Inactivo
                                    </option>
                                    <option value="pendiente" <?= $curso['estado'] === 'pendiente' ? 'selected' : '' ?>>
                                        <i class="fas fa-clock"></i> Pendiente
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label required-field">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="4" 
                                  placeholder="Describa detalladamente el contenido y objetivos del curso..." required><?= htmlspecialchars($curso['descripcion']) ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <input type="text" class="form-control" id="categoria" name="categoria" 
                                       value="<?= htmlspecialchars($curso['categoria'] ?? '') ?>" 
                                       placeholder="Ej: Programación, Diseño, Marketing">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="instructor" class="form-label">Instructor</label>
                                <input type="text" class="form-control" id="instructor" name="instructor" 
                                       value="<?= htmlspecialchars($curso['instructor'] ?? '') ?>" 
                                       placeholder="Nombre completo del instructor">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detalles del Curso -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-cogs"></i>
                        <span>Detalles del Curso</span>
                    </h4>
                    
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label for="fecha_inicio" class="form-label required-field">Fecha de Inicio</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                       value="<?= $curso['fecha_inicio'] ?>" required>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label for="duracion_horas" class="form-label required-field">Duración (horas)</label>
                                <input type="number" class="form-control" id="duracion_horas" name="duracion_horas" 
                                       min="1" max="1000" value="<?= $curso['duracion_horas'] ?>" 
                                       placeholder="Ej: 40" required>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label for="precio" class="form-label required-field">Precio ($)</label>
                                <input type="number" class="form-control" id="precio" name="precio" 
                                       min="0" step="0.01" value="<?= $curso['precio'] ?>" 
                                       placeholder="0.00" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modalidad" class="form-label">Modalidad</label>
                        <select class="form-select" id="modalidad" name="modalidad">
                            <option value="">Seleccionar modalidad</option>
                            <option value="presencial" <?= ($curso['modalidad'] ?? '') === 'presencial' ? 'selected' : '' ?>>
                                🏢 Presencial
                            </option>
                            <option value="virtual" <?= ($curso['modalidad'] ?? '') === 'virtual' ? 'selected' : '' ?>>
                                💻 Virtual
                            </option>
                            <option value="hibrida" <?= ($curso['modalidad'] ?? '') === 'hibrida' ? 'selected' : '' ?>>
                                🔄 Híbrida
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Contenido Adicional -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-list-ul"></i>
                        <span>Contenido Adicional</span>
                    </h4>
                    
                    <div class="mb-3">
                        <label for="objetivos" class="form-label">Objetivos del Curso</label>
                        <textarea class="form-control" id="objetivos" name="objetivos" rows="4" 
                                  placeholder="• Objetivo 1&#10;• Objetivo 2&#10;• Objetivo 3..."><?= htmlspecialchars($curso['objetivos'] ?? '') ?></textarea>
                        <div class="form-text">
                            <i class="fas fa-lightbulb me-1"></i>
                            Describe los objetivos principales que los estudiantes alcanzarán al completar el curso.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="requisitos" class="form-label">Requisitos Previos</label>
                        <textarea class="form-control" id="requisitos" name="requisitos" rows="4" 
                                  placeholder="• Conocimientos básicos de...&#10;• Experiencia en...&#10;• Software requerido..."><?= htmlspecialchars($curso['requisitos'] ?? '') ?></textarea>
                        <div class="form-text">
                            <i class="fas fa-clipboard-check me-1"></i>
                            Especifica los conocimientos, habilidades o herramientas necesarias antes de tomar el curso.
                        </div>
                    </div>
                </div>

                <!-- Imagen del Curso -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-image"></i>
                        <span>Imagen del Curso</span>
                    </h4>
                    
                    <?php if (!empty($curso['imagen_url'])): ?>
                        <div class="current-image-container">
                            <label class="form-label">
                                <i class="fas fa-photo-video me-2"></i>Imagen Actual
                            </label>
                            <div class="text-center">
                                <div class="image-preview-container">
                                    <img src="../../.<?= htmlspecialchars($curso['imagen_url']) ?>" 
                                         alt="Imagen actual del curso" class="preview-image" id="imagen-actual">
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="image-upload-section" id="upload-section">
                        <div class="mb-3">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted mb-3">
                                <?php if (!empty($curso['imagen_url'])): ?>
                                    Cambiar Imagen del Curso
                                <?php else: ?>
                                    Subir Imagen del Curso
                                <?php endif; ?>
                            </h5>
                            <input type="file" class="form-control" id="imagen" name="imagen" 
                                   accept="image/jpeg,image/png,image/gif,image/webp">
                            <div class="form-text mt-3">
                                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                                    <span><i class="fas fa-check-circle text-success me-1"></i>Formatos: JPG, PNG, GIF, WebP</span>
                                    <span><i class="fas fa-weight-hanging text-info me-1"></i>Tamaño máximo: 5MB</span>
                                    <span><i class="fas fa-expand-arrows-alt text-primary me-1"></i>Recomendado: 800x600px</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="preview-nueva-imagen" class="current-image-container" style="display: none;">
                        <label class="form-label">
                            <i class="fas fa-eye me-2"></i>Vista Previa de la Nueva Imagen
                        </label>
                        <div class="text-center">
                            <div class="image-preview-container">
                                <img id="nueva-imagen-preview" alt="Vista previa" class="preview-image">
                                <button type="button" class="remove-image" id="btn-remove-preview" title="Remover imagen">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="form-section">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                        <a href="../cursos.php" class="btn btn-secondary order-2 order-md-1">
                            <i class="fas fa-times"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary order-1 order-md-2" id="btn-submit">
                            <i class="fas fa-save"></i>Actualizar Curso
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>    
    <script>
        $(document).ready(function() {
            // Drag and drop para imágenes
            const uploadSection = $('#upload-section');
            const imageInput = $('#imagen');

            // Prevenir comportamiento por defecto del drag and drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadSection[0].addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });

            // Destacar área de drop cuando se arrastra un archivo
            ['dragenter', 'dragover'].forEach(eventName => {
                uploadSection[0].addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                uploadSection[0].addEventListener(eventName, unhighlight, false);
            });

            // Manejar archivo soltado
            uploadSection[0].addEventListener('drop', handleDrop, false);

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            function highlight() {
                uploadSection.addClass('dragover');
            }

            function unhighlight() {
                uploadSection.removeClass('dragover');
            }

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                
                if (files.length > 0) {
                    imageInput[0].files = files;
                    handleImagePreview(files[0]);
                }
            }

            // Vista previa de imagen
            imageInput.change(function() {
                const file = this.files[0];
                if (file) {
                    handleImagePreview(file);
                }
            });

            function handleImagePreview(file) {
                // Validar tipo de archivo
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    showAlert('Tipo de archivo no permitido. Solo se permiten JPG, PNG, GIF y WebP.', 'danger');
                    imageInput.val('');
                    return;
                }

                // Validar tamaño
                const maxSize = 5 * 1024 * 1024; // 5MB
                if (file.size > maxSize) {
                    showAlert('El archivo es demasiado grande. El tamaño máximo permitido es 5MB.', 'danger');
                    imageInput.val('');
                    return;
                }

                // Mostrar vista previa
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#nueva-imagen-preview').attr('src', e.target.result);
                    $('#preview-nueva-imagen').show();
                    
                    // Scroll suave hacia la vista previa
                    $('#preview-nueva-imagen')[0].scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                };
                reader.readAsDataURL(file);
            }

            // Remover vista previa
            $('#btn-remove-preview').click(function() {
                imageInput.val('');
                $('#preview-nueva-imagen').hide();
                unhighlight();
            });

            // Validación del formulario mejorada
            $('#form-editar-curso').on('submit', function(e) {
                e.preventDefault();
                
                let isValid = true;
                const submitBtn = $('#btn-submit');
                
                // Limpiar validaciones anteriores
                $('.form-control, .form-select').removeClass('is-invalid is-valid');
                
                // Validar campos requeridos
                $(this).find('[required]').each(function() {
                    const field = $(this);
                    const value = field.val().trim();
                    
                    if (!value) {
                        field.addClass('is-invalid');
                        isValid = false;
                    } else {
                        field.addClass('is-valid');
                    }
                });

                // Validaciones específicas
                const duracion = parseInt($('#duracion_horas').val());
                if (duracion <= 0) {
                    $('#duracion_horas').addClass('is-invalid');
                    isValid = false;
                }

                const precio = parseFloat($('#precio').val());
                if (precio < 0) {
                    $('#precio').addClass('is-invalid');
                    isValid = false;
                }

                if (!isValid) {
                    showAlert('Por favor, complete todos los campos requeridos correctamente.', 'danger');
                    
                    // Scroll al primer campo con error
                    const firstError = $('.is-invalid').first();
                    if (firstError.length) {
                        firstError[0].scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'center' 
                        });
                        firstError.focus();
                    }
                    return false;
                }

                // Mostrar estado de carga
                submitBtn.addClass('loading').prop('disabled', true);
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i>Actualizando...');
                
                // Enviar formulario
                setTimeout(() => {
                    this.submit();
                }, 500);
            });

            // Remover clases de validación cuando el usuario escriba
            $('input, textarea, select').on('input change', function() {
                $(this).removeClass('is-invalid is-valid');
            });

            // Función para mostrar alertas
            function showAlert(message, type = 'info') {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-${type === 'danger' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : 'info-circle'} me-3 mt-1"></i>
                            <div class="flex-grow-1">
                                ${message}
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                
                $('.page-content').prepend(alertHtml);
                
                // Scroll hacia arriba para mostrar la alerta
                $('html, body').animate({
                    scrollTop: 0
                }, 300);
                
                // Auto-dismiss después de 5 segundos
                setTimeout(() => {
                    $('.alert').fadeOut();
                }, 5000);
            }

            // Mejorar la experiencia del usuario con tooltips
            $('[title]').tooltip();

            // Auto-resize para textareas
            $('textarea').on('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });

            // Formatear precio automáticamente
            $('#precio').on('blur', function() {
                const value = parseFloat($(this).val());
                if (!isNaN(value)) {
                    $(this).val(value.toFixed(2));
                }
            });

            // Capitalizar primera letra de campos de texto
            $('#nombre, #categoria, #instructor').on('blur', function() {
                const value = $(this).val();
                if (value) {
                    $(this).val(value.charAt(0).toUpperCase() + value.slice(1));
                }
            });

            // Confirmación antes de salir si hay cambios
            let formChanged = false;
            
            $('input, textarea, select').on('change input', function() {
                formChanged = true;
            });

            $('a[href="../cursos.php"]').on('click', function(e) {
                if (formChanged) {
                    if (!confirm('Hay cambios sin guardar. ¿Está seguro de que desea salir?')) {
                        e.preventDefault();
                    }
                }
            });

            // Prevenir doble envío
            let formSubmitted = false;
            $('#form-editar-curso').on('submit', function() {
                if (formSubmitted) {
                    return false;
                }
                formSubmitted = true;
            });
        });
    </script>
</body>
</html>