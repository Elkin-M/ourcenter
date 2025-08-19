# Sistema de Gestión de Inscripciones - OurCenter

## Descripción
Sistema completo para la gestión de inscripciones de estudiantes a cursos, incluyendo gestión de pagos, estados y notificaciones.

## Características Principales

### ✅ Funcionalidades Implementadas
- **Gestión de Inscripciones**: Crear, editar, eliminar y ver inscripciones
- **Gestión de Estudiantes**: Lista de estudiantes con búsqueda y filtros
- **Gestión de Cursos**: Selección de cursos con información detallada
- **Sistema de Pagos**: Seguimiento de pagos pendientes y completados
- **Estados de Inscripción**: Activa, Pendiente, Cancelada, Completa
- **Filtros Avanzados**: Por estado, pago y búsqueda global
- **Estadísticas en Tiempo Real**: Dashboard con métricas actualizadas
- **Interfaz Responsiva**: Diseño moderno y adaptable a dispositivos móviles

### 🔧 Tecnologías Utilizadas
- **Frontend**: HTML5, CSS3, JavaScript, jQuery, Bootstrap 5
- **Backend**: PHP 7.4+, PDO para base de datos
- **Base de Datos**: MySQL/MariaDB
- **Librerías**: DataTables, SweetAlert2, Font Awesome

## Instalación

### 1. Requisitos Previos
- PHP 7.4 o superior
- MySQL 5.7 o MariaDB 10.2 o superior
- Servidor web (Apache/Nginx)
- Composer (opcional, para dependencias)

### 2. Configuración de Base de Datos

#### Opción A: Usar el archivo SQL proporcionado
```bash
# Importar las tablas y datos de ejemplo
mysql -u root -p ourcenter < database/inscripciones_tables.sql
```

#### Opción B: Crear manualmente
1. Crear la base de datos `ourcenter`
2. Ejecutar el contenido del archivo `database/inscripciones_tables.sql`

### 3. Configuración del Sistema

#### Editar configuración de base de datos
```php
// Archivo: server/config.php
return [
    'base_url' => 'http://localhost:8080/ourcenter/',
    'app_name' => 'OurCenter',
    'db' => [
        'host' => 'localhost',
        'dbname' => 'ourcenter',
        'user' => 'root',
        'password' => '', // Tu contraseña de MySQL
    ],
    // ... resto de configuración
];
```

#### Configurar permisos de archivos
```bash
# Asegurar que los directorios sean escribibles
chmod 755 templates/admin/ajax/
chmod 644 templates/admin/ajax/*.php
```

### 4. Acceso al Sistema

#### Credenciales por defecto
- **Email**: admin@ourcenter.com
- **Contraseña**: admin123
- **Rol**: Administrador

## Estructura de Archivos

```
ourcenter/
├── templates/admin/
│   ├── inscripciones.php          # Página principal de inscripciones
│   └── ajax/
│       ├── cargar_datos_inscripcion.php    # Cargar estudiantes y cursos
│       ├── crear_inscripcion.php           # Crear nuevas inscripciones
│       └── cargar_inscripciones.php        # Cargar lista de inscripciones
├── config/
│   ├── db.php                     # Configuración de base de datos
│   └── init.php                   # Inicialización de sesión
├── server/
│   └── config.php                 # Configuración general
└── database/
    └── inscripciones_tables.sql   # Estructura de base de datos
```

## Uso del Sistema

### 1. Acceso al Panel de Administración
1. Navegar a `http://localhost:8080/ourcenter/templates/admin/inscripciones.php`
2. Iniciar sesión con las credenciales de administrador

### 2. Crear Nueva Inscripción
1. Hacer clic en "Nueva Inscripción"
2. Seleccionar un curso del dropdown
3. Buscar y seleccionar estudiantes
4. Configurar estado inicial y opciones
5. Hacer clic en "Crear Inscripciones"

### 3. Gestionar Inscripciones Existentes
- **Ver Detalles**: Hacer clic en el ícono de ojo
- **Editar**: Hacer clic en el ícono de lápiz
- **Eliminar**: Hacer clic en el ícono de papelera

### 4. Filtros y Búsqueda
- **Búsqueda Global**: Buscar por nombre, email o curso
- **Filtro por Estado**: Activa, Pendiente, Cancelada, Completa
- **Filtro por Pago**: Pagado, Pendiente, Sin pago
- **Reiniciar Filtros**: Botón para limpiar todos los filtros

## Base de Datos

### Tablas Principales

#### `inscripciones`
- Almacena las inscripciones de estudiantes a cursos
- Estados: Activa, Pendiente, Cancelada, Completa
- Métodos: Presencial, Online, Telefónica

#### `pagos`
- Seguimiento de pagos por inscripción
- Estados: Pendiente, Completado, Cancelado, Reembolsado
- Incluye fechas de vencimiento y métodos de pago

#### `usuarios`
- Usuarios del sistema (estudiantes, profesores, administradores)
- Roles: admin, profesor, estudiante

#### `cursos`
- Catálogo de cursos disponibles
- Incluye precios, duración y salones asignados

#### `salones`
- Información de salones de clase
- Capacidad y ubicación

## Personalización

### Modificar Estilos
Los estilos están en el archivo `templates/admin/inscripciones.php` dentro de la sección `<style>`.

### Agregar Nuevos Estados
1. Modificar el enum en la tabla `inscripciones`
2. Actualizar las opciones en el formulario
3. Agregar estilos CSS para el nuevo estado

### Configurar Notificaciones por Email
1. Configurar SMTP en `server/config.php`
2. Implementar la función `enviarNotificacionInscripcion()` en `crear_inscripcion.php`

## Solución de Problemas

### Error de Conexión a Base de Datos
- Verificar credenciales en `server/config.php`
- Asegurar que MySQL esté ejecutándose
- Verificar que la base de datos `ourcenter` exista

### Error 500 en AJAX
- Verificar permisos de archivos
- Revisar logs de error de PHP
- Asegurar que las sesiones estén configuradas correctamente

### Datos No Se Cargan
- Verificar que las tablas existan en la base de datos
- Revisar consultas SQL en los archivos AJAX
- Verificar que haya datos de ejemplo insertados

## Seguridad

### Medidas Implementadas
- Validación de sesiones de administrador
- Prepared statements para prevenir SQL injection
- Validación de datos de entrada
- Headers de seguridad en respuestas AJAX

### Recomendaciones Adicionales
- Usar HTTPS en producción
- Implementar rate limiting
- Configurar firewall de base de datos
- Realizar backups regulares

## Soporte

Para soporte técnico o reportar problemas:
1. Revisar los logs de error
2. Verificar la configuración de base de datos
3. Comprobar que todas las dependencias estén instaladas

## Licencia

Este proyecto es parte del sistema OurCenter y está sujeto a los términos de licencia correspondientes.
