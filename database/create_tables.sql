-- Script SQL para crear las tablas necesarias

-- Tabla de cursos
CREATE TABLE IF NOT EXISTS cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    duracion_semanas INT DEFAULT 0,
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Tabla de salones
CREATE TABLE IF NOT EXISTS salones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    capacidad_maxima INT NOT NULL DEFAULT 20,
    horario VARCHAR(255),
    fecha_inicio DATE,
    fecha_fin DATE,
    estado ENUM('Activo', 'Inactivo', 'Completo') DEFAULT 'Activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (curso_id) REFERENCES cursos(id)
);

-- Tabla de estudiantes
CREATE TABLE IF NOT EXISTS estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    apellido VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    documento_identidad VARCHAR(50),
    fecha_nacimiento DATE,
    direccion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Tabla de inscripciones
CREATE TABLE IF NOT EXISTS inscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    salon_id INT NOT NULL,
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('Activa', 'Pendiente', 'Cancelada', 'Completa') DEFAULT 'Pendiente',
    metodo_inscripcion ENUM('Presencial', 'Online', 'Telefónica') DEFAULT 'Online',
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id),
    FOREIGN KEY (salon_id) REFERENCES salones(id),
    UNIQUE KEY unique_inscripcion (estudiante_id, salon_id, deleted_at)
);

-- Tabla de pagos
CREATE TABLE IF NOT EXISTS pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inscripcion_id INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metodo_pago ENUM('Efectivo', 'Tarjeta', 'Transferencia', 'PayPal') DEFAULT 'Efectivo',
    estado ENUM('Pendiente', 'Completado', 'Cancelado') DEFAULT 'Pendiente',
    referencia_pago VARCHAR(255),
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (inscripcion_id) REFERENCES inscripciones(id)
);

-- Insertar datos de ejemplo
INSERT INTO cursos (nombre, descripcion, duracion_semanas) VALUES
('Desarrollo Web Frontend', 'Aprende HTML, CSS, JavaScript y frameworks modernos', 8),
('Python para Principiantes', 'Introducción completa al lenguaje Python', 6),
('Diseño UX/UI Avanzado', 'Diseño de experiencias de usuario profesionales', 12),
('Marketing Digital', 'Estrategias de marketing en el mundo digital', 10);

INSERT INTO salones (curso_id, nombre, descripcion, precio, capacidad_maxima, horario) VALUES
(1, 'Frontend - Mañana', 'Salón matutino de desarrollo web', 299.00, 25, 'Lunes a Viernes 9:00-12:00'),
(1, 'Frontend - Tarde', 'Salón vespertino de desarrollo web', 299.00, 25, 'Lunes a Viernes 14:00-17:00'),
(2, 'Python - Básico', 'Salón básico de Python', 199.00, 30, 'Martes y Jueves 18:00-21:00'),
(3, 'UX/UI - Profesional', 'Salón avanzado de diseño', 399.00, 20, 'Sábados 9:00-17:00'),
(4, 'Marketing - Intensivo', 'Salón intensivo de marketing', 249.00, 15, 'Lunes a Miércoles 19:00-22:00');

INSERT INTO estudiantes (nombre, apellido, email, telefono, documento_identidad) VALUES
('María', 'García Rodríguez', 'maria.garcia@email.com', '+57 300 123 4567', '12345678'),
('Carlos', 'López Martínez', 'carlos.lopez@email.com', '+57 310 987 6543', '87654321'),
('Ana Sofía', 'Herrera', 'ana.herrera@email.com', '+57 320 456 7890', '11223344'),
('Diego', 'Ramírez', 'diego.ramirez@email.com', '+57 315 555 1234', '44332211'),
('Laura', 'Moreno', 'laura.moreno@email.com', '+57 301 777 8888', '99887766');
?>