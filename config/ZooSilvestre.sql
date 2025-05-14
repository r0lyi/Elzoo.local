-- Tabla de Usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'usuario') DEFAULT 'usuario',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Animales (Actualizada)
CREATE TABLE animales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    nombre_cientifico VARCHAR(255),
    clase VARCHAR(100), -- (mamíferos, aves, reptiles, etc.)
    continente VARCHAR(100),
    habitat VARCHAR(255),
    dieta VARCHAR(100),
    peso VARCHAR(50), -- Puede variar, así que VARCHAR es más flexible (ej: "50-70 kg")
    tamano VARCHAR(50), -- Similar al peso (ej: "1.5 metros de largo")
    informacion TEXT, -- Descripción más detallada del animal
    sabias TEXT, -- Curiosidad del animal
    imagen VARCHAR(255), -- URL de la imagen obtenida del scraping
    fecha_nacimiento DATE,
    sexo ENUM('macho', 'hembra', 'desconocido') DEFAULT 'desconocido',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Noticias (Actualizada)
CREATE TABLE noticias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL, -- Una descripción o extracto de la noticia
    fecha_publicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    url_origen VARCHAR(255) UNIQUE NOT NULL, -- URL de la noticia original
    imagen VARCHAR(255), -- URL de la imagen obtenida del scraping
    autor_id INT, -- ID del usuario que publicó la noticia (solo administradores)
    FOREIGN KEY (autor_id) REFERENCES usuarios(id)
);

-- Tabla de Foros (Publicaciones de Usuarios)
CREATE TABLE foros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    contenido TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    autor_id INT NOT NULL,
    FOREIGN KEY (autor_id) REFERENCES usuarios(id)
);

-- Tabla de Comentarios del Foro
CREATE TABLE comentarios_foro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    foro_id INT NOT NULL,
    autor_id INT NOT NULL,
    contenido TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (foro_id) REFERENCES foros(id) ON DELETE CASCADE,
    FOREIGN KEY (autor_id) REFERENCES usuarios(id)
);

-- Tabla de Adopciones
CREATE TABLE adopciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    animal_id INT NOT NULL,
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'aprobada', 'rechazada') DEFAULT 'pendiente',
    comentarios TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (animal_id) REFERENCES animales(id),
    UNIQUE KEY `uk_usuario_animal` (`usuario_id`, `animal_id`)
);

-- Tabla de Contactos (para mensajes de los usuarios al zoo)
CREATE TABLE contactos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    asunto VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('nuevo', 'leido', 'respondido') DEFAULT 'nuevo'
);