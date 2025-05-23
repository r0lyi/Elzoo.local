// public/js/admin.js
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.admin-sidebar .nav-link');
    const adminDynamicContent = document.getElementById('admin-dynamic-content'); // El contenedor donde inyectaremos el HTML

    // Función para cargar el contenido del componente de forma asíncrona
    async function loadComponentAsync(url, componentName) {
        // Muestra un mensaje de carga (opcional)
        adminDynamicContent.innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-3">Cargando...</p></div>';

        try {
            // Realiza la petición AJAX al servidor
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // Importante para que el servidor detecte la petición AJAX
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const html = await response.text(); // Espera el HTML como texto
            adminDynamicContent.innerHTML = html; // Inyecta el HTML en el contenedor

            // Actualiza la URL en la barra de direcciones sin recargar la página
            history.pushState({ component: componentName }, '', url);

            // Opcional: Ejecutar scripts que estén en el HTML cargado
            // Esto es importante si tus componentes tienen scripts inline o que deben ejecutarse después de ser insertados
            // Más info aquí: https://stackoverflow.com/questions/6109990/executing-script-elements-inserted-via-innerhtml
            const scripts = adminDynamicContent.querySelectorAll('script');
            scripts.forEach(script => {
                const newScript = document.createElement('script');
                if (script.src) {
                    newScript.src = script.src;
                } else {
                    newScript.textContent = script.textContent;
                }
                document.body.appendChild(newScript).parentNode.removeChild(newScript);
            });


        } catch (error) {
            console.error("Error al cargar el componente:", error);
            adminDynamicContent.innerHTML = '<div class="alert alert-danger" role="alert">Error al cargar el contenido. Por favor, inténtalo de nuevo.</div>';
        }
    }

    // Manejador de clics en los enlaces del sidebar
    navLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault(); // Evita la recarga completa de la página

            // Remover la clase 'active' de todos los enlaces
            navLinks.forEach(l => l.classList.remove('active'));
            // Añadir la clase 'active' al enlace clicado
            this.classList.add('active');

            const url = this.getAttribute('href'); // Obtiene la URL (ej. /admin/users)
            const componentName = this.getAttribute('data-component'); // Obtiene el nombre del componente (ej. usuarioAdmin)

            loadComponentAsync(url, componentName);
        });
    });

    // Manejar el botón de atrás/adelante del navegador
    window.addEventListener('popstate', function(event) {
        // La URL ya ha cambiado, solo necesitamos cargar el contenido correcto
        const url = window.location.pathname;
        let componentName = null;
        // Encuentra el data-component basado en la URL actual
        navLinks.forEach(link => {
            if (link.getAttribute('href') === url) {
                componentName = link.getAttribute('data-component');
                // Actualiza la clase activa en el menú
                navLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            }
        });

        // Si es la ruta /admin base, maneja el caso por defecto
        if (url === '/admin' || url === '/admin/') {
            // Podrías recargar el dashboard por defecto o dejar el HTML existente
            // Por simplicidad, recargaremos la página si se vuelve al dashboard principal
            // window.location.reload(); // O puedes hacer que tu PHP devuelva el HTML del dashboard
            adminDynamicContent.innerHTML = `
                <h2><i class="fas fa-tachometer-alt"></i> Dashboard Administrativo</h2>
                <div class="alert alert-info mt-4" role="alert">
                    Bienvenido al panel de administración de MyZoo. Utiliza el menú de la izquierda para navegar por las diferentes secciones de gestión.
                </div>
            `;
            // Asegúrate de que ningún enlace esté activo si estás en el dashboard
            navLinks.forEach(l => l.classList.remove('active'));
        } else if (componentName) {
            loadComponentAsync(url, componentName); // Carga el componente si se encuentra
        }
    });

    // Lógica para la carga inicial de la página
    // Si la URL ya es de un componente (ej. /admin/users) al cargar la página directamente
    const initialUrl = window.location.pathname;
    let initialComponent = null;

    if (initialUrl !== '/admin' && initialUrl !== '/admin/') { // No es el dashboard principal
        navLinks.forEach(link => {
            if (link.getAttribute('href') === initialUrl) {
                initialComponent = link.getAttribute('data-component');
                link.classList.add('active'); // Activa el enlace en el sidebar
            }
        });
        // Si el componente ya fue incluido por PHP (en la primera carga), no necesitamos AJAX para su HTML
        // pero podrías querer una función para cargar los datos en este punto si tuvieras APIs
        // Actualmente, solo muestra el mensaje que PHP ya incluyó en el componente.
    }
    // Si la URL es /admin, el contenido por defecto ya está en admin.html.twig
});