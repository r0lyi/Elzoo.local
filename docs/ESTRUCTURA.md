¡Entendido! Aquí tienes el contenido en Markdown para el archivo docs/ESTRUCTURA.md, explicando la estructura de carpetas que hemos definido:

Markdown

# Estructura del Proyecto

Este documento describe la organización de las carpetas y archivos principales dentro del proyecto del Zoo Virtual. La estructura se ha diseñado para separar claramente la lógica del backend de la API de la interfaz de usuario del frontend, facilitando el desarrollo, el mantenimiento y la escalabilidad.

zoo_virtual/
├── api/
│   ├── Config/
│   │   └── database.php
│   ├── Controllers/
│   │   └── AnimalController.php
│   │   └── HabitatController.php
│   │   └── ... (otros controladores de la API)
│   ├── Core/
│   │   └── ... (Clases base o núcleo de la API)
│   ├── Models/
│   │   └── Animal.php
│   │   └── Habitat.php
│   │   └── ... (otros modelos de datos)
│   ├── Routes/
│   │   └── api.php
│   └── public/
│       └── index.php
│       └── .htaccess (Opcional)
├── frontend/
│   ├── templates/
│   │   ├── animales/
│   │   │   └── listar.html.twig
│   │   │   └── ver.html.twig
│   │   ├── habitats/
│   │   │   └── listar.html.twig
│   │   │   └── ver.html.twig
│   │   ├── base.html.twig
│   │   ├── home.twig
│   │   └── ... (otras plantillas del frontend)
│   ├── public/
│   │   ├── css/
│   │   │   └── bootstrap.min.css
│   │   │   └── estilos.css
│   │   ├── js/
│   │   │   └── bootstrap.bundle.min.js
│   │   │   └── script.js
│   │   ├── img/
│   │   │   └── ... (Imágenes del frontend)
│   │   └── index.php
│   │   └── .htaccess (Opcional)
│   └── src/ (Opcional)
├── vendor/
│   ├── autoload.php
│   ├── composer/
│   ├── symfony/
│   ├── twbs/
│   └── twig/
├── composer.json
├── composer.lock
└── README.md


## Descripción de las Carpetas Principales

### `api/`

Este directorio contiene toda la lógica del backend de la API (Application Programming Interface). Su función principal es proporcionar los datos y funcionalidades que el frontend consumirá.

* **`Config/`**: Aquí residen los archivos de configuración de la API, como la configuración de la conexión a la base de datos (`database.php`).
* **`Controllers/`**: Los controladores son responsables de recibir las peticiones a la API, interactuar con los modelos para obtener o manipular datos y devolver las respuestas apropiadas (generalmente en formato JSON). Ejemplos:
    * `AnimalController.php`: Maneja las peticiones relacionadas con los animales (listar, crear, ver detalles, etc.).
    * `HabitatController.php`: Maneja las peticiones relacionadas con los hábitats.
    * `...`: Otros controladores para diferentes recursos de la API.
* **`Core/`**: Esta carpeta puede contener las clases base o el núcleo de tu micro-framework o las funcionalidades compartidas por varios componentes de la API.
* **`Models/`**: Los modelos representan la estructura de los datos de tu aplicación y contienen la lógica para interactuar con la base de datos. Cada modelo suele corresponder a una tabla de la base de datos. Ejemplos:
    * `Animal.php`: Define la estructura de los datos de un animal y los métodos para interactuar con la tabla de animales.
    * `Habitat.php`: Define la estructura de los datos de un hábitat y sus métodos de interacción con la base de datos.
    * `...`: Otros modelos para las diferentes entidades de tu zoo virtual.
* **`Routes/`**: El archivo `api.php` dentro de esta carpeta define las rutas (endpoints) de la API y las asocia a las acciones específicas de los controladores. Por ejemplo, una ruta `/api/animales` podría estar asociada al método `listar()` del `AnimalController`.
* **`public/`**: Este es el directorio público para la API. El archivo `index.php` es el punto de entrada principal para todas las peticiones que llegan a la API. Cualquier archivo dentro de esta carpeta es accesible públicamente a través de la URL base de la API. Un archivo `.htaccess` opcional puede usarse para configurar la reescritura de URLs, la seguridad u otras directivas del servidor web para la API.

### `frontend/`

Este directorio contiene todo lo relacionado con la interfaz de usuario que los usuarios verán e interactuarán en sus navegadores. Está construido utilizando las plantillas Twig y el framework CSS Bootstrap.

* **`templates/`**: Contiene los archivos de plantillas Twig (`.html.twig`). Estas plantillas definen la estructura HTML de las páginas y cómo se mostrarán los datos. Las plantillas suelen organizarse en subcarpetas por sección de la aplicación para mantener el orden. Ejemplos:
    * `animales/`: Plantillas relacionadas con la visualización de animales (listado, detalles).
    * `habitats/`: Plantillas relacionadas con la visualización de hábitats (listado, detalles).
    * `base.html.twig`: La plantilla base que define la estructura HTML común de todas las páginas del frontend (incluyendo la inclusión de Bootstrap).
    * `home.twig`: La plantilla para la página principal del zoo virtual.
    * `...`: Otras plantillas para las diferentes vistas del frontend.
* **`public/`**: Este es el directorio público para el frontend. Contiene todos los recursos estáticos que el navegador necesita para renderizar la interfaz de usuario.
    * **`css/`**: Contiene los archivos CSS, incluyendo la librería Bootstrap (`bootstrap.min.css`) y cualquier estilo personalizado que desarrolles (`estilos.css`).
    * **`js/`**: Contiene los archivos JavaScript, incluyendo el bundle de Bootstrap (`bootstrap.bundle.min.js`) y cualquier script personalizado para la interactividad del frontend (`script.js`).
    * **`img/`**: Almacena las imágenes utilizadas en el frontend (logos, fotos de animales, etc.).
    * `index.php`: El punto de entrada principal para todas las peticiones al frontend. Este archivo se encarga de inicializar Twig, cargar la plantilla solicitada y mostrar la página al usuario.
    * `.htaccess` (Opcional): Puede usarse para configurar la reescritura de URLs (para URLs más amigables), la gestión de la caché u otras directivas del servidor web para el frontend.
* **`src/` (Opcional)**: Este directorio puede contener clases PHP específicas del frontend, como helpers para las plantillas Twig o lógica de presentación más compleja que no reside directamente en las plantillas ni en el backend.

### Otros Archivos y Carpetas

* **`vendor/`**: Este directorio es gestionado por Composer y contiene todas las dependencias de tu proyecto, incluyendo la librería de plantillas Twig (`twig/twig`) y el framework CSS Bootstrap (`twbs/bootstrap`).
* **`composer.json`**: Archivo que define las dependencias de tu proyecto y otra información del paquete para Composer.
* **`composer.lock`**: Archivo que registra las versiones exactas de las dependencias que están instaladas en el proyecto. Es importante incluir este archivo en el control de versiones.
* **`README.md`**: Un archivo que proporciona una visión general del proyecto, instrucciones de instalación y uso, y otra información importante para los desarrolladores y usuarios.

## Configuración del `.htaccess`

En este proyecto, hemos utilizado archivos `.htaccess` dentro de los directorios `public/` tanto para la API (`/api/public/.htaccess`) como para el frontend (`/frontend/public/.htaccess`). El archivo `.htaccess` es un archivo de configuración utilizado por los servidores web Apache para definir reglas a nivel de directorio.

**¿Por qué usar `.htaccess` en los directorios `public/`?**

1.  **Punto de Entrada Único:** El objetivo principal de estos archivos `.htaccess` es redirigir todas las peticiones que no correspondan a archivos o directorios existentes a los respectivos archivos `index.php`. Esto permite que nuestra API y nuestro frontend tengan un único punto de entrada para manejar todas las solicitudes de manera controlada.
    * En el **frontend**, `/frontend/public/index.php` se encarga de inicializar Twig y enrutar las peticiones a las diferentes plantillas para mostrar la interfaz de usuario.
    * En la **API**, `/api/public/index.php` se encarga de recibir la petición, analizarla y delegarla al controlador adecuado para procesarla y devolver una respuesta (generalmente en formato JSON).

2.  **URLs Amigables (Reescritura de URLs):** Aunque la configuración actual se centra en el punto de entrada único, el `.htaccess` también sienta las bases para futuras reescrituras de URLs. Esto nos permitirá tener URLs más limpias y semánticas (por ejemplo, `/animales/1` en lugar de `/index.php?p=animales&id=1`) tanto en el frontend como en la API.

3.  **Contexto Específico:** Al colocar los archivos `.htaccess` dentro de sus respectivos directorios `public/`, las reglas de reescritura se aplican únicamente a las peticiones dentro de ese contexto. Esto evita posibles conflictos o comportamientos inesperados entre las reglas de la API y el frontend. Por ejemplo, las reglas para las rutas de la API no afectarán las rutas del frontend y viceversa.

**Contenido de los archivos `.htaccess`:**

Ambos archivos `.htaccess` (en `/api/public/` y `/frontend/public/`) tienen una estructura similar:

```apacheconf
# Habilitar el motor de reescritura
RewriteEngine On

# Establecer la base para este contexto (API o Frontend)
RewriteBase /api/public/  # Para la API
RewriteBase /frontend/public/ # Para el Frontend

# Excluir solicitudes para archivos o directorios existentes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Redirigir todas las demás solicitudes a index.php
RewriteRule ^(.*)$ index.php [L,QSA]
RewriteEngine On: Activa la funcionalidad de reescritura de URLs del servidor Apache.
RewriteBase: Define la ruta base para las reglas de reescritura dentro de este directorio. Es crucial que esté configurado correctamente para cada contexto.
RewriteCond %{REQUEST_FILENAME} !-f: Esta condición verifica que la solicitud no sea para un archivo existente en el servidor.
RewriteCond %{REQUEST_FILENAME} !-d: Esta condición verifica que la solicitud no sea para un directorio existente en el servidor.
RewriteRule ^(.*)$ index.php [L,QSA]: Si las condiciones anteriores se cumplen (es decir, la solicitud no es para un archivo o directorio existente), esta regla redirige la solicitud al archivo index.php. Las banderas [L,QSA] indican:
L (Last): Indica que esta es la última regla que se procesará para esta solicitud.
QSA (Query String Append): Asegura que cualquier parámetro en la URL original se añada a la URL de destino (index.php).
En resumen, el uso de archivos .htaccess en los directorios public/ de nuestra API y frontend nos proporciona un punto de entrada único para cada aplicación y sienta las bases para futuras funcionalidades como URLs amigables, manteniendo la lógica de enrutamiento centralizada en nuestros archivos index.php respectivos.
