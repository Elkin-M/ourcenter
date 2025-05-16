<?php
require_once __DIR__ . '/../config/db.php';

class Curso {
    /**
     * Obtiene todos los cursos activos
     */
    public static function obtenerTodos() {
        $conexion = conectarDB();
        
        $query = "SELECT * FROM cursos WHERE estado IN ('en_inscripcion', 'activo') ORDER BY id";
        $resultado = $conexion->query($query);
        
        $cursos = [];
        if ($resultado && $resultado->num_rows > 0) {
            while ($curso = $resultado->fetch_assoc()) {
                $cursos[] = $curso;
            }
        }
        
        $conexion->close();
        return $cursos;
    }
    
    /**
     * Obtiene un curso por su ID
     */
    public static function obtenerPorId($id) {
        $conexion = conectarDB();
        
        $id = $conexion->real_escape_string($id);
        $query = "SELECT * FROM cursos WHERE id = $id LIMIT 1";
        $resultado = $conexion->query($query);
        
        $curso = null;
        if ($resultado && $resultado->num_rows > 0) {
            $curso = $resultado->fetch_assoc();
        }
        
        $conexion->close();
        return $curso;
    }
    
    /**
     * Formatea el precio para mostrar
     */
    public static function formatearPrecio($precio) {
        return number_format($precio, 0, ',', '.');
    }
    
    /**
     * Convierte la duración en horas a un formato más amigable
     */
    public static function formatearDuracion($horas) {
        if ($horas <= 0) {
            return "N/A";
        }
        
        // Convertir a meses (estimado)
        $meses = ceil($horas / 30);
        return $meses . ($meses == 1 ? " mes" : " meses");
    }
    
    /**
     * Formatea el nivel para mostrar
     */
    public static function formatearNivel($nivel) {
        switch ($nivel) {
            case 'básico':
                return "Básico";
            case 'intermedio':
                return "Intermedio";
            case 'avanzado':
                return "Avanzado";
            case 'todos':
                return "Todos los niveles";
            default:
                return ucfirst($nivel);
        }
    }
    
    /**
     * Genera el rango de edades para mostrar
     */
    public static function rangoEdades($min, $max) {
        if ($min == $max) {
            return "$min años";
        } elseif ($max >= 99) {
            return "$min años en adelante";
        } else {
            return "$min - $max años";
        }
    }
}