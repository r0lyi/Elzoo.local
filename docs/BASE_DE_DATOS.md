# Esquema y Lógica de Negocio de la Base de Datos

Este documento detalla la estructura de la base de datos utilizada en el proyecto del Zoo Virtual, explicando la lógica de negocio detrás de cada tabla y sus interrelaciones.

## Diagrama Entidad-Relación (ERD)

![Diagrama ERD](../../frontend/public/img/DiagramaMYSQL.png)

Este diagrama proporciona una representación visual de las tablas (entidades) en nuestra base de datos y las relaciones (conexiones) entre ellas. Se indican las claves primarias (PK), las claves foráneas (FK) y la cardinalidad de las relaciones (uno-a-uno, uno-a-muchos, muchos-a-muchos).

## Lógica de Negocio de las Tablas

### `usuarios`

* **Lógica de Negocio:** Almacena la información de los usuarios que interactúan con la plataforma. Se distingue entre administradores (con permisos para crear noticias y gestionar el sitio) y usuarios regulares (que pueden participar en foros y realizar solicitudes de adopción). La tabla gestiona la autenticación (a través de la contraseña hasheada) y el rol de cada usuario.
* **Campos Principales:**
    * `id`: Identificador único del usuario.
    * `nombre`: Nombre visible del usuario.
    * `email`: Dirección de correo electrónico única del usuario (para identificación y comunicación).
    * `password`: Contraseña del usuario almacenada de forma segura (hasheada).
    * `rol`: Indica si el usuario es un 'admin' o un 'usuario', controlando sus permisos.
    * `fecha_registro`: Fecha en que el usuario se registró en la plataforma.

### `animales`

* **Lógica de Negocio:** Contiene los detalles de cada animal presente en el zoo virtual. Esta tabla es la base para mostrar la información de los animales al público. Los datos se obtienen mediante scraping de fuentes externas.
* **Campos Principales:**
    * `id`: Identificador único del animal.
    * `nombre`: Nombre común del animal.
    * `nombre_cientifico`: Nombre científico del animal.
    * `clase`: Clasificación taxonómica del animal (ej: mamíferos, aves).
    * `continente`: Continente de origen del animal.
    * `habitat`: Descripción del entorno natural donde vive el animal.
    * `dieta`: Tipo de alimentación del animal.
    * `peso`: Rango o valor del peso del animal.
    * `tamano`: Descripción del tamaño del animal.
    * `informacion`: Información detallada sobre el animal.
    * `sabias`: Un dato curioso o interesante sobre el animal.
    * `imagen`: URL de la imagen del animal obtenida del scraping.
    * `fecha_nacimiento`: Fecha de nacimiento del animal (si está disponible en los datos scrapeados).
    * `sexo`: Sexo del animal.
    * `fecha_registro`: Fecha en que se añadió la información del animal a la base de datos.

### `noticias`

* **Lógica de Negocio:** Almacena las noticias y novedades relacionadas con el zoo. Estas noticias se obtienen principalmente mediante scraping de fuentes externas, pero también pueden ser creadas por administradores. La tabla mantiene un registro de la fuente original de la noticia.
* **Campos Principales:**
    * `id`: Identificador único de la noticia.
    * `titulo`: Título de la noticia.
    * `descripcion`: Un breve resumen o descripción de la noticia.
    * `fecha_publicacion`: Fecha de publicación de la noticia (obtenida del scraping o al crearla).
    * `url_origen`: URL de la noticia original (fuente del scraping).
    * `imagen`: URL de la imagen asociada a la noticia (obtenida del scraping).
    * `autor_id`: ID del usuario administrador que creó la noticia (si fue creada internamente).

### `foros`

* **Lógica de Negocio:** Permite a los usuarios registrados interactuar y discutir sobre temas relacionados con el zoo y los animales. Cada entrada en esta tabla es un tema de discusión iniciado por un usuario.
* **Campos Principales:**
    * `id`: Identificador único del tema del foro.
    * `titulo`: Título del tema del foro.
    * `contenido`: El mensaje inicial del tema del foro.
    * `fecha_creacion`: Fecha y hora en que se creó el tema.
    * `autor_id`: ID del usuario que creó el tema (clave foránea a la tabla `usuarios`).

### `comentarios_foro`

* **Lógica de Negocio:** Almacena las respuestas y comentarios dentro de cada tema del foro. Permite a los usuarios participar en las discusiones. Cada comentario está asociado a un tema del foro y a un usuario.
* **Campos Principales:**
    * `id`: Identificador único del comentario.
    * `foro_id`: ID del tema del foro al que pertenece el comentario (clave foránea a la tabla `foros`).
    * `autor_id`: ID del usuario que escribió el comentario (clave foránea a la tabla `usuarios`).
    * `contenido`: El texto del comentario.
    * `fecha_creacion`: Fecha y hora en que se creó el comentario.

### `adopciones`

* **Lógica de Negocio:** Gestiona las solicitudes de adopción de animales por parte de los usuarios registrados. Permite a los usuarios expresar su interés en adoptar un animal específico y al sistema rastrear el estado de estas solicitudes.
* **Campos Principales:**
    * `id`: Identificador único de la solicitud de adopción.
    * `usuario_id`: ID del usuario que realiza la solicitud (clave foránea a la tabla `usuarios`).
    * `animal_id`: ID del animal que se desea adoptar (clave foránea a la tabla `animales`).
    * `fecha_solicitud`: Fecha y hora en que se realizó la solicitud.
    * `estado`: Estado actual de la solicitud ('pendiente', 'aprobada', 'rechazada').
    * `comentarios`: Comentarios adicionales del usuario al realizar la solicitud.
    * **Clave Única (`uk_usuario_animal`)**: Asegura que un usuario no pueda solicitar la adopción del mismo animal varias veces.

### `contactos`

* **Lógica de Negocio:** Almacena los mensajes que los usuarios envían al zoo a través del formulario de contacto. Permite al personal del zoo revisar y responder a las consultas de los usuarios.
* **Campos Principales:**
    * `id`: Identificador único del mensaje de contacto.
    * `nombre`: Nombre del remitente del mensaje.
    * `email`: Dirección de correo electrónico del remitente.
    * `asunto`: Asunto del mensaje.
    * `mensaje`: Contenido del mensaje.
    * `fecha_envio`: Fecha y hora en que se envió el mensaje.
    * `estado`: Estado del mensaje ('nuevo', 'leido', 'respondido') para el seguimiento interno.

Esta documentación proporciona una visión general de la estructura de la base de datos y la lógica de negocio detrás de cada tabla. El diagrama ERD complementa esta descripción visualizando las relaciones entre las tablas.