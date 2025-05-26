# **📚 Documentación Técnica Extendida del Proyecto Zoo Silvestre**

Este documento proporciona una inmersión profunda en la arquitectura, los componentes clave y las convenciones de desarrollo de `Elzoo.local`. Está dirigido a desarrolladores que deseen entender, mantener o extender el proyecto.

---

## **1\. Estructura del Proyecto**

`Elzoo.local` sigue un patrón de arquitectura **Model-View-Controller (MVC) básico** y modular. Esta organización facilita la separación de responsabilidades y la escalabilidad del proyecto.

* .  
* ├── composer.json            \# Define las dependencias de PHP y scripts del proyecto.  
* ├── composer.lock            \# Bloquea las versiones exactas de las dependencias instaladas.  
* ├── config/                  \# Archivos de configuración de la aplicación.  
* │   └── Elzoo.sql     \# Esquema de la base de datos MySQL/MariaDB.  
* ├── controllers/             \# Contiene la lógica de negocio y el manejo de solicitudes.  
* │   ├── ControllerAdmin.php      \# Lógica del panel de administración general y por secciones.  
* │   ├── ControllerAnimalDetail.php \# Muestra detalles de un animal específico.  
* │   ├── ControllerCookie.php     \# Utilidades para la gestión de cookies (usadas para JWT).  
* │   ├── ControllerDatabase.php   \# Centraliza la conexión y gestión de la base de datos (PDO).  
* │   ├── ControllerForoDetail.php \# Maneja la visualización y comentarios de posts del foro.  
* │   ├── ControllerForo.php       \# Gestiona el listado y creación de posts del foro.  
* │   ├── ControllerHome.php       \# Lógica para la página de inicio.  
* │   ├── ControllerJWT.php        \# Utilidades para la generación, verificación y decodificación de JWTs.  
* │   ├── ControllerList.php       \# Controladores para listados generales (ej., animales, noticias).  
* │   ├── ControllerLogin.php      \# Lógica para el inicio de sesión de usuarios.  
* │   ├── ControllerPerfil.php     \# Gestión del perfil del usuario.  
* │   ├── ControllerRegister.php   \# Lógica para el registro de nuevos usuarios.  
* │   ├── ControllerTwig.php       \# Configuración y renderizado de plantillas Twig.  
* │   └── http/                    \# \*\*Controladores específicos para endpoints de API RESTful.\*\*  
* │       ├── AnimalesController.php       \# API para la gestión de animales.  
* │       ├── ComentarioForoController.php \# API para la gestión de comentarios de foro.  
* │       ├── ForoController.php           \# API para la gestión de foros.  
* │       ├── NoticiasController.php       \# API para la gestión de noticias.  
* │       └── UsuariosController.php       \# API para la gestión de usuarios.  
* ├── DOCUMENTACION.md         \# Documentación técnica detallada (este archivo).  
* ├── models/                  \# Clases que representan las entidades de la base de datos y su lógica de negocio.  
* │   ├── Animales.php         \# Modelo para la tabla \`animales\`.  
* │   ├── ComentarioForo.php   \# Modelo para la tabla \`comentarios\_foro\`.  
* │   ├── Foro.php             \# Modelo para la tabla \`foros\`.  
* │   ├── Noticias.php         \# Modelo para la tabla \`noticias\`.  
* │   └── Usuarios.php         \# Modelo para la tabla \`usuarios\`.  
* ├── public/                  \# El "document root" del servidor web. Contiene assets públicos y el punto de entrada principal.  
* │   ├── css/                 \# Hojas de estilo CSS.  
* │   │   └── admin.css        \# Estilos específicos para el panel de administración.  
* │   ├── images/              \# Directorio para imágenes del proyecto.  
* │   ├── index.php            \# El "Front Controller" principal. Todas las solicitudes pasan por aquí.  
* │   └── js/                  \# Scripts JavaScript.  
* │       ├── animales.js      \# JS para interacciones en la sección de animales.  
* │       ├── comentario.js    \# JS para funcionalidades de comentarios (ej. validación, interacción con API).  
* │       ├── foros.js         \# JS para interacciones en la sección de foros.  
* │       ├── noticias.js      \# JS para interacciones en la sección de noticias.  
* │       └── usuarios.js      \# JS para interacciones en la sección de usuarios.  
* ├── README.md                \# Descripción general del proyecto y guía de inicio rápido.  
* ├── routes/                  \# Define los mapeos de URL a controladores/acciones.  
* │   ├── api.php              \# Rutas para la API RESTful.  
* │   └── web.php              \# Rutas para las páginas web renderizadas por Twig.  
* └── views/                   \# Plantillas Twig para la renderización del HTML.  
*     ├── 404.html.twig        \# Plantilla para páginas no encontradas.  
*     ├── admin/               \# Plantillas específicas para las subsecciones del panel de administración.  
*     │   ├── animales.html.twig  
*     │   ├── foros.html.twig  
*     │   ├── noticias.html.twig  
*     │   └── usuarios.html.twig  
*     ├── admin.html.twig      \# Plantilla principal del panel de administración.  
*     ├── animal\_detalle.html.twig \# Detalles de un animal.  
*     ├── base.html.twig       \# Plantilla base para todas las páginas web (estructura común).  
*     ├── footer.html.twig     \# Fragmento de pie de página.  
*     ├── foro\_detalle.html.twig \# Detalles de un post del foro, incluyendo comentarios.  
*     ├── foro.html.twig       \# Listado de posts del foro.  
*     ├── header.html.twig     \# Fragmento de cabecera.  
*     ├── home.html.twig       \# Plantilla de la página de inicio.  
*     ├── listaAnimales.html.twig \# Listado de animales.  
*     ├── login.html.twig      \# Formulario de inicio de sesión.  
*     ├── perfil.html.twig     \# Página de perfil de usuario.  
*     └── register.html.twig   \# Formulario de registro de usuario.  
    
  ---

  ## **2\. API**

El proyecto incluye una API RESTful para la gestión programática de recursos, lo que permite a un frontend (o futuras aplicaciones) interactuar con los datos de forma estructurada.

### **2.1 Endpoints (Ejemplos Comunes)**

Los controladores en `controllers/http/` se encargan de manejar estas rutas. La respuesta estándar es JSON.

* **`GET /api/v1/animales`**  
  * **Descripción:** Obtiene un listado de todos los animales.  
  * **Parámetros de Consulta:**  
    * `limit` (opcional): Número máximo de animales a devolver.  
    * `offset` (opcional): Desplazamiento desde el inicio del resultado.  
    * `nombre` (opcional): Filtra por nombre de animal (búsqueda parcial).  
    * `especie` (opcional): Filtra por especie.  
* **Respuesta Exitosa (200 OK):**  
   JSON  
  \[  
*     {  
*         "id": 1,  
*         "nombre": "León Africano",  
*         "especie": "Panthera leo",  
*         "descripcion": "...",  
*         "imagen\_url": "/images/leon.jpg"  
*     }  
* \]  
  *   
* **`GET /api/v1/animales/{id}`**  
  * **Descripción:** Obtiene los detalles de un animal específico por su ID.  
  * **Parámetros de Ruta:** `{id}` (entero, ID del animal).  
  * **Respuesta Exitosa (200 OK):** Objeto JSON del animal.  
  * **Respuesta de Error (404 Not Found):** `{"error": "Animal no encontrado."}`  
* **`POST /api/v1/animales`**  
  * **Descripción:** Crea un nuevo animal. Requiere autenticación de administrador.  
* **Cuerpo de la Solicitud (JSON):**  
   JSON  
  {  
*     "nombre": "Elefante Asiático",  
*     "especie": "Elephas maximus",  
*     "descripcion": "...",  
*     "imagen\_url": "/images/elefante.jpg"  
* }  
  *   
  * **Respuesta Exitosa (201 Created):** `{"id": 20, "message": "Animal creado con éxito."}`  
  * **Respuestas de Error:** `400 Bad Request`, `401 Unauthorized`, `403 Forbidden`  
* **`PUT /api/v1/animales/{id}`**  
  * **Descripción:** Actualiza un animal existente por su ID. Requiere autenticación de administrador.  
  * **Parámetros de Ruta:** `{id}` (entero, ID del animal).  
  * **Cuerpo de la Solicitud (JSON):** Campos a actualizar.  
  * **Respuesta Exitosa (200 OK):** `{"message": "Animal actualizado con éxito."}`  
* **`DELETE /api/v1/animales/{id}`**  
  * **Descripción:** Elimina un animal por su ID. Requiere autenticación de administrador.  
  * **Parámetros de Ruta:** `{id}` (entero, ID del animal).  
  * **Respuesta Exitosa (204 No Content):** No devuelve cuerpo.  
* **`GET /api/v1/foros`**  
  * **Descripción:** Lista todos los posts del foro con datos del autor.  
* **`GET /api/v1/foros/{id}/comentarios`**  
  * **Descripción:** Lista los comentarios para un post de foro específico, incluyendo datos del autor.  
* **`POST /api/v1/foros/{id}/comentarios`**  
  * **Descripción:** Crea un nuevo comentario para un post de foro. Requiere autenticación de usuario.  
  * **Cuerpo de la Solicitud (JSON):** `{"contenido": "Mi comentario..."}`

*(**Nota:** Esta es una selección de endpoints. La implementación completa puede variar según el desarrollo exacto de cada `Controller` en `controllers/http/` y las rutas definidas en `routes/api.php`.)*

### **2.2 Autenticación de la API**

La autenticación para la API RESTful se gestiona mediante **JSON Web Tokens (JWT)**.

1. **Obtención del Token:** Un usuario (o cliente de API) debe iniciar sesión a través de un endpoint como `POST /api/v1/login` (no incluido explícitamente en el `web.php` proporcionado, pero sería un complemento lógico para una API completa).  
   * **Cuerpo de la Solicitud (JSON):** `{"email": "user@example.com", "password": "securepassword"}`  
* **Respuesta Exitosa (200 OK):**  
   JSON  
  {  
*     "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",  
*     "user": {"id": 1, "nombre": "...", "rol": "usuario"}  
* }  
  *   
* **Uso del Token:** Una vez obtenido el token, debe incluirse en los **encabezados de cada solicitud protegida** (es decir, solicitudes que requieren autenticación) en el formato `Authorization: Bearer <TU_JWT_TOKEN>`.  
   Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...  
2.   
3. **Verificación:** El servidor decodifica y verifica el JWT utilizando la clave secreta (`$secret_key`). Si el token es válido y no ha expirado, la solicitud se procesa. Si es inválido o falta, se devuelve una respuesta `401 Unauthorized` o `403 Forbidden`.  
   ---

   ## **3\. Interfaz**

El frontend de `Elzoo.local` se construye utilizando plantillas Twig renderizadas en el servidor, complementadas con JavaScript para interacciones dinámicas.

### **3.1 Plantillas Twig**

* **Organización:** Las plantillas se encuentran en la carpeta `views/`. Utilizan la herencia de plantillas de Twig para mantener la consistencia y reducir la duplicación de código.  
* **`base.html.twig`:** La plantilla raíz. Define la estructura HTML (`<html>`, `<head>`, `<body>`), incluyendo el `header.html.twig`, `footer.html.twig` y un bloque `{% block content %}` principal donde se inyecta el contenido específico de cada página.  
* **`header.html.twig`:** Contiene la barra de navegación, el logo y los enlaces principales.  
* **`footer.html.twig`:** Incluye información de derechos de autor, enlaces a redes sociales, etc.  
* **Plantillas Principales por Sección:**  
  * `home.html.twig`: Contenido de la página principal.  
  * `listaAnimales.html.twig`: Muestra el listado de animales.  
  * `animal_detalle.html.twig`: Presenta los detalles de un animal individual.  
  * `foro.html.twig`: Muestra la lista de posts del foro.  
  * `foro_detalle.html.twig`: Muestra un post del foro específico y sus comentarios, incluyendo el formulario para añadir nuevos comentarios.  
  * `login.html.twig`, `register.html.twig`, `perfil.html.twig`: Páginas de autenticación y perfil de usuario.  
  * `admin.html.twig` y `views/admin/*.html.twig`: Plantillas para el panel de administración, cada una con su propia lógica de tablas/formularios para gestionar datos.

  ### **3.2 Activos (CSS, JS, Imágenes)**

Todos los assets estáticos se encuentran en la carpeta `public/`.

* **CSS (`public/css/`):** Contiene hojas de estilo personalizadas. Se asume que el diseño principal se basa en un framework CSS como **Bootstrap 5.x**, que probablemente se carga a través de un CDN en `base.html.twig`. `admin.css` proporciona estilos específicos para el panel de administración.  
* **JavaScript (`public/js/`):** Contiene archivos JavaScript separados para cada sección de la aplicación, promoviendo la modularidad.  
  * `animales.js`: Puede incluir lógica para filtros de búsqueda, carruseles, o interacciones con la página de animales.  
  * `comentario.js`: Podría manejar la validación de formularios de comentarios en el lado del cliente o enviar comentarios a la API de forma asíncrona (AJAX).  
  * `foros.js`, `noticias.js`, `usuarios.js`: Similares, para funcionalidades específicas de sus respectivas secciones.  
* **Imágenes (`public/images/`):** Almacena todas las imágenes utilizadas en la aplicación (logos, fotos de animales, etc.).

  ### **3.3 Interacción con la API**

Aunque la mayoría de las páginas web se renderizan en el servidor, los archivos JavaScript en `public/js/` pueden consumir los endpoints de la API (`/api/v1/...`) para funcionalidades dinámicas sin recargar la página.

* **Ejemplos de Uso:**  
  * **Búsqueda en tiempo real:** Un campo de búsqueda en el listado de animales o foros podría enviar solicitudes `GET` a la API para filtrar los resultados sin una recarga completa de la página.  
  * **Envío de formularios asíncronos:** Aunque el formulario de comentarios en `foro_detalle.html.twig` usa un POST tradicional seguido de un redirect, futuras mejoras podrían permitir enviar comentarios vía AJAX para una experiencia de usuario más fluida, consumiendo `POST /api/v1/foros/{id}/comentarios`.  
  * **Operaciones CRUD en el Admin Panel:** Los formularios y tablas dentro del panel de administración podrían utilizar JavaScript para enviar solicitudes `POST`, `PUT`, `DELETE` a los endpoints de la API, actualizando la interfaz sin recargas.  
* **Manejo de Tokens:** El JavaScript del cliente necesitaría extraer el JWT de la cookie (o de alguna variable global si se pasa desde el servidor) y adjuntarlo a los encabezados `Authorization: Bearer` para las solicitudes a la API protegidas.  
  ---

  ## **4\. Base de Datos**

La base de datos del proyecto se modela para soportar las funcionalidades del zoo, noticias, foro y gestión de usuarios. El esquema se encuentra en `config/ZooSilvestre.sql`.

* **Sistema de Gestión:** MySQL / MariaDB.

* **`ControllerDatabase.php`:** Centraliza la conexión y el manejo de PDO.

* **Tablas Clave:**

  * `usuarios`:  
    * `id`(PK, INT, INCREMENTO AUTOMÁTICO)  
    * `nombre`(VARCHAR)  
    * `email`(VARCHAR, ÚNICO)  
    * `password`(VARCHAR, hash)  
    * `rol`(ENUM('usuario', 'admin'), PREDETERMINADO 'usuario')  
    * `fecha_registro`(FECHA Y HORA, MARCA DE TIEMPO ACTUAL PREDETERMINADA)  
    * `token` (VARCHAR, NULLABLE, para restablecer contraseña u otras funciones de token)  
  * `animales`:  
    * `id`(PK, INT, INCREMENTO AUTOMÁTICO)  
    * `nombre`(VARCHAR)  
    * `especie`(VARCHAR)  
    * `descripcion`(TEXTO)  
    * `imagen_url` (VARCHAR, URL a la imagen del animal)  
    * `fecha_creacion`(FECHA Y HORA, MARCA DE TIEMPO ACTUAL PREDETERMINADA)  
  * `foros`:  
    * `id`(PK, INT, INCREMENTO AUTOMÁTICO)  
    * `titulo`(VARCHAR)  
    * `contenido`(TEXTO)  
    * `autor_id`(INT, FK a `usuarios.id`)  
    * `fecha_creacion`(FECHA Y HORA, MARCA DE TIEMPO ACTUAL PREDETERMINADA)  
  * `comentarios_foro`:  
    * `id`(PK, INT, INCREMENTO AUTOMÁTICO)  
    * `foro_id`(INT, FK a `foros.id`)  
    * `autor_id`(INT, FK a `usuarios.id`)  
    * `contenido`(TEXTO)  
    * `fecha_creacion`(FECHA Y HORA, MARCA DE TIEMPO ACTUAL PREDETERMINADA)  
  * `noticias`:  
    * `id`(PK, INT, INCREMENTO AUTOMÁTICO)  
    * `titulo`(VARCHAR)  
    * `contenido`(TEXTO)  
    * `autor_id`(INT, FK a `usuarios.id`)  
    * `fecha_publicacion`(FECHA Y HORA, MARCA DE TIEMPO ACTUAL PREDETERMINADA)  
    * `imagen_url`(VARCHAR, ACEPTABLE COMO NULL)

  ---

  ## **5\. Contribución**

¡Nos encantaría que contribuyeras al proyecto `Elzoo.local`\! Sigue estas directrices para asegurar un proceso de contribución fluido.

1. **Reporte de Issues:** Si encuentras un error o tienes una sugerencia, por favor, abre un "Issue" en el repositorio de GitHub. Proporciona una descripción clara y, si es posible, pasos para reproducir el problema.  
2. **Proceso de Desarrollo:**  
   * Haz un "fork" del repositorio a tu cuenta de GitHub.  
* Clona tu "bifurcación" en tu máquina local.  
   Intento  
  git clone https://github.com/tu-usuario/Elzoo.local.git  
* cd Elzoo.local  
  *   
* Crea una nueva rama para tus cambios. Utiliza un nombre descriptivo (ej., `feature/añadir-galeria-animales`).  
   Intento  
  git checkout \-b feature/nombre-de-tu-rama  
  *   
  * Realiza tus cambios en el código.  
  * Asegúrate de que el código sigue las convenciones de estilo existentes y de que todas las funcionalidades anteriores sigan operativas.  
* Escribe "commits" claros y concisos.  
   Intento  
  git commit \-m "feat: \[Descripción breve de tu característica\]"  
* \# o  
* git commit \-m "fix: \[Descripción breve de tu corrección\]"  
  *   
* Envía tus cambios a tu "fork" en GitHub.  
   Intento  
  git push origin feature/nombre-de-tu-rama  
  *   
  * Abre un "Pull Request" (PR) desde tu rama a la rama `main` del repositorio original.  
    * Proporciona una descripción detallada de tus cambios.  
    * Haz referencia a cualquier "Issue" relevante.  
1. **Convenciones de Código:**  
   * **PHP:** Sigue las directrices PSR-12 para el estilo de código (formato, nombres de clases, etc.).  
   * **JavaScript:** Utiliza convenciones de nombres camelCase y asegúrate de que el código sea claro y modular.  
   * **Twig:** Mantén las plantillas limpias, legibles y bien indentadas. Utiliza la herencia de plantillas de Twig para evitar la duplicación.  
   * **Nomenclatura:** Utiliza nombres de variables, funciones y clases descriptivos y significativos.

   ---

   ## **6\. Consideraciones de Seguridad**

La seguridad es fundamental en cualquier aplicación web. Este proyecto implementa varias medidas de seguridad, pero es crucial mantenerlas actualizadas y mejorarlas continuamente.

* **Consultas Preparadas (PDO):** Todos los modelos utilizan PDO con consultas preparadas, previniendo eficazmente ataques de inyección SQL.  
* **Hash de Contraseñas:** Las contraseñas de usuario se almacenan como hashes utilizando funciones seguras (ej., `password_hash()` de PHP) para evitar la exposición en caso de una brecha de datos.  
* **JSON Web Tokens (JWT):** Utilizados para la autenticación, con tokens almacenados en cookies HTTP-only para mayor seguridad contra ataques XSS. Es vital usar una `$secret_key` robusta y segura.  
* **Sanitización y Escape de Salida:** Los datos provenientes de la base de datos o de la entrada del usuario deben ser sanitizados antes de ser procesados y escapados antes de ser mostrados en las plantillas Twig (usando el filtro `|e`) para prevenir ataques de Cross-Site Scripting (XSS).  
* **Validación de Entrada:** La validación de todos los datos recibidos del usuario se realiza en el lado del servidor para garantizar su integridad y prevenir entradas maliciosas.  
* **Manejo de Errores y Logging:** Los errores no se muestran directamente al usuario en producción (`display_errors = Off`) y se registran en un archivo de log para auditoría y depuración.  
* 
