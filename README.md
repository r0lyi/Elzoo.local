# **🐘 Elzoo.local: Tu Portal Interactivo del Zoo Silvestre**

¡Bienvenido a `Elzoo.local`\! Este proyecto es una aplicación web sencilla y modular diseñada para simular una comunidad zoológica. Permite a los visitantes explorar información sobre animales, estar al día con las últimas noticias del zoo, y participar en un foro interactivo. Los usuarios registrados pueden contribuir al foro, mientras que los administradores tienen control total sobre el contenido del sitio.

Para una documentación técnica detallada sobre la arquitectura, la base de datos y la configuración avanzada, consulta el [**Directorio de Documentación**](DOCUMENTACION.md).

---

## **📂 Estructura del Proyecto (Resumen)**

El proyecto sigue una arquitectura **Model-View-Controller (MVC) básica** para una gestión clara de la lógica y la presentación.

* `public/`: El corazón de la aplicación, contiene `index.php` (el punto de entrada principal), así como todos los archivos CSS, JavaScript e imágenes accesibles públicamente.  
* `routes/`: Define cómo las URLs se mapean a la lógica de la aplicación (`web.php` para páginas, `api.php` para endpoints de datos).  
* `controllers/`: Aloja la lógica que procesa las solicitudes del usuario, interactúa con los modelos y prepara los datos para las vistas.  
* `models/`: Contiene las clases que representan las entidades de la base de datos (animales, usuarios, foros, etc.) y la lógica para interactuar con ellas.  
* `views/`: Almacena todas las plantillas HTML (Twig) que definen la interfaz de usuario.  
* `config/`: Archivos de configuración importantes, incluido el esquema de la base de datos.  
* `vendor/`: Dependencias de PHP gestionadas por Composer (como Twig).

---

## **⚙️ Instalación**

Para poner en marcha `Elzoo.local` en tu entorno local, sigue estos pasos:

**Clona el repositorio:**  
 Intento  
git clone https://github.com/r0lyi/Elzoo.local.git  
cd Elzoo.local

1. 

**Instala las dependencias de PHP:**  
 Intento  
composer install

2.   
3. **Configura la base de datos:**  
   * Crea una base de datos MySQL/MariaDB (ej. `elzoo_db`).  
   * Importa el esquema SQL desde `config/ZooSilvestre.sql` a tu nueva base de datos.  
   * **Actualiza las credenciales de la base de datos** en `controllers/ControllerDatabase.php` para que coincidan con tu configuración local.  
4. **Define tu clave secreta JWT:** Por seguridad, establece una clave secreta única para JWT en un lugar seguro de tu código (por ejemplo, en un archivo de configuración que no esté en el repositorio, o directamente en `index.php` para desarrollo). **¡No uses valores por defecto en producción\!**  
5. **Configura tu servidor web (Apache/Nginx):** Asegúrate de que el **document root** apunte a la carpeta `public/` del proyecto. Esto es crucial para el enrutamiento correcto y la seguridad.  
   * Para Apache, puedes usar el `.htaccess` proporcionado en `public/`.

Para Nginx, necesitarás una configuración similar a la siguiente:  
 Nginx  
server {  
    listen 80;  
    server\_name elzoo.local; \# Tu dominio local

    root /ruta/a/Elzoo.local/public; \# ¡Ajusta esta ruta\!  
    index index.php;

    location / {  
        try\_files $uri $uri/ /index.php?$query\_string;  
    }

    location \~ \\.php$ {  
        include fastcgi\_params;  
        fastcgi\_pass unix:/var/run/php/php8.x-fpm.sock; \# Reemplaza con tu versión de PHP-FPM  
        fastcgi\_index index.php;  
        fastcgi\_param SCRIPT\_FILENAME $document\_root$fastcgi\_script\_name;  
    }  
}

* 

---

## **🚀 Uso**

Una vez instalado y configurado, abre tu navegador y visita `http://localhost/` (o el dominio que hayas configurado, como `http://elzoo.local/`).

* **Explora:** Navega por las secciones de **Animales**, **Noticias** y el **Foro**.  
* **Regístrate/Inicia Sesión:** Crea una cuenta para poder interactuar en el foro (crear publicaciones y comentarios).  
* **Panel de Administración:** Si has configurado un usuario con rol de administrador, podrás acceder al panel en `/admin` para gestionar usuarios, animales, foros y noticias.
