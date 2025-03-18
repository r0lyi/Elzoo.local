-- Crear la base de datos
CREATE DATABASE ZooSilvestre;
USE ZooConnect;

-- Tabla para usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    contraseña_hash VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('usuario', 'administrador') DEFAULT 'usuario'
    token VARCHAR(255),
);

-- Tabla para datos de animales
CREATE TABLE animales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    especie VARCHAR(100) NOT NULL,
    -- hábitat VARCHAR(100) NOT NULL,
    --descripción TEXT,
    imagen TEXT
);

-- Tabla para adopciones
CREATE TABLE adopciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    animal_id INT NOT NULL,
    estado ENUM('Pendiente', 'Aprobada', 'Rechazada') DEFAULT 'Pendiente',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (animal_id) REFERENCES animales(id) ON DELETE CASCADE
);

-- Tabla para publicaciones del foro
CREATE TABLE publicaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    título VARCHAR(255) NOT NULL,
    contenido TEXT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla para noticias
CREATE TABLE noticias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titular VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    imagen TEXT-- Ruta o nombre del archivo de imagen
   
);
