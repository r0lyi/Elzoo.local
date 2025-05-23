// Archivo: /public/js/noticia.js

// Este código se ejecutará cuando el script sea cargado dinámicamente por admin.js
console.log("noticia.js cargado");

const API_NOTICIAS_URL = '/api/v1/noticias'; // !!! ASEGÚRATE DE QUE ESTA RUTA RELATIVA A TU WEBROOT (public) SEA CORRECTA !!!

// --- Funciones de Utilidad (Pueden estar en un archivo helpers.js compartido en public/js/) ---
// Copia si no tienes un archivo compartido, o si no quieres depender de usuarios.js/animales.js
// para ellas.
function showMessage(element, message, type) {
    if (element) {
        element.textContent = message;
        element.className = `message ${type}`;
        element.style.display = 'block';
    }
}

function hideMessage(element) {
     if (element) {
        element.style.display = 'none';
        element.textContent = '';
        element.className = 'message';
     }
}

function clearForm(form) {
    if (form) form.reset();
}

// Helper para formatear fecha/hora para input[type=datetime-local]
// Espera un string de fecha/hora en formato compatible con Date (ej: ISO 8601)
function formatDateTimeLocal(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    // Check if the date is valid
    if (isNaN(date.getTime())) {
        console.error("Fecha inválida para formatear:", dateString);
        return dateString; // Retorna original si no es válida
    }
    // Formato YYYY-MM-DDTHH:mm para input[type=datetime-local]
    const year = date.getFullYear();
    const month = ('0' + (date.getMonth() + 1)).slice(-2);
    const day = ('0' + date.getDate()).slice(-2);
    const hours = ('0' + date.getHours()).slice(-2);
    const minutes = ('0' + date.getMinutes()).slice(-2);
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

// --- CRUD: Listar Noticias (Función de inicialización llamada por admin.js) ---

async function loadNoticias() {
    console.log("Cargando noticias...");
    // Referencias a elementos del DOM específicos de la sección noticias
    const newsTableBody = document.getElementById('newsTableBody');
    const newsListMessage = document.getElementById('newsListMessage');
    const editNewsSection = document.getElementById('editNewsSection');

    if (!newsTableBody || !newsListMessage) {
         console.error('Elementos de tabla de noticias o mensajes no encontrados al intentar cargar datos.');
         return;
    }

    hideMessage(newsListMessage);
    if(editNewsSection) editNewsSection.style.display = 'none'; // Ocultar form de edición al cargar la lista
    newsTableBody.innerHTML = ''; // Limpiar la tabla antes de cargar

    try {
        const response = await fetch(API_NOTICIAS_URL);
        const data = await response.json();

        if (!response.ok) {
            showMessage(newsListMessage, `Error al cargar noticias: ${data.message || response.statusText}`, 'error');
            return;
        }

        if (data.length === 0) {
            newsTableBody.innerHTML = '<tr><td colspan="6">No hay noticias registradas.</td></tr>'; // 6 columnas
            return;
        }

        // Renderizar noticias en la tabla
        data.forEach(noticia => {
            const row = newsTableBody.insertRow();
            // Formatear fecha para mostrar si es necesario
            const fechaDisplay = noticia.fecha_publicacion ? new Date(noticia.fecha_publicacion).toLocaleString() : 'N/A';

            row.innerHTML = `
                <td>${noticia.id}</td>
                <td>${noticia.imagen ? `<img src="${noticia.imagen}" alt="${noticia.titulo}" style="width: 50px; height: auto;">` : 'Sin Imagen'}</td>
                <td>${noticia.titulo}</td>
                <td>${fechaDisplay}</td>
                <td><a href="${noticia.url_origen}" target="_blank" rel="noopener noreferrer">Enlace</a></td> {# Enlace al origen #}
                <td>
                    <button class="button-warning edit-news-button" data-news-id="${noticia.id}">Editar</button>
                    <button class="button-danger delete-news-button" data-news-id="${noticia.id}">Eliminar</button>
                </td>
            `;
        });

    } catch (error) {
        console.error('Error fetching noticias:', error);
        showMessage(newsListMessage, 'Ocurrió un error al conectar con la API para cargar noticias.', 'error');
    }
}

// --- Exponer la función de inicialización globalmente ---
// El script admin.js la llamará después de cargar este archivo
window.loadNoticias = loadNoticias;


// --- Event Listeners (Se adjuntan cuando este script se ejecuta y el DOM está listo) ---
document.addEventListener('DOMContentLoaded', () => {
     console.log("DOMContentLoaded en noticia.js");

    // Obtener referencias AHORA que el DOM está garantizado
    const newsTableBody = document.getElementById('newsTableBody');
    const addNewsForm = document.getElementById('addNewsForm');
    const addNewsMessage = document.getElementById('addNewsMessage');

    const editNewsSection = document.getElementById('editNewsSection');
    const editNewsForm = document.getElementById('editNewsForm');
    const editNewsId = document.getElementById('editNewsId');
    const editNewsTitulo = document.getElementById('editNewsTitulo');
    const editNewsDescripcion = document.getElementById('editNewsDescripcion');
    const editNewsFechaPublicacion = document.getElementById('editNewsFechaPublicacion');
    const editNewsUrlOrigen = document.getElementById('editNewsUrlOrigen');
    const editNewsImagen = document.getElementById('editNewsImagen');
    // Referencias a otros campos si los añadiste en el parcial
    const editNewsMessage = document.getElementById('editNewsMessage');
    const cancelEditNewsButton = document.getElementById('cancelEditNewsButton');


    // --- CRUD: Añadir Noticia ---
    if (addNewsForm) {
        addNewsForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideMessage(addNewsMessage);

            const formData = new FormData(addNewsForm);
            const newsData = Object.fromEntries(formData.entries());

             // Convertir el formato datetime-local a un formato compatible con tu backend si es necesario
             // La API de Noticias recibe un string de fecha/hora válido. El formato de input datetime-local
             // ('YYYY-MM-DDTHH:mm') suele ser compatible con muchos backends (ej: ISO 8601 parcial)
             // pero confirma si tu PHP/BD espera un formato exacto. Si necesita otro, formatealo aquí.
             // Ejemplo (si necesitaras ISO 8601 completo con segundos y Z):
             // if (newsData.fecha_publicacion) {
             //     newsData.fecha_publicacion = new Date(newsData.fecha_publicacion).toISOString();
             // }


            try {
                const response = await fetch(API_NOTICIAS_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(newsData)
                });
                const result = await response.json();

                if (response.status === 201) {
                    showMessage(addNewsMessage, 'Noticia creada con éxito.', 'success');
                    clearForm(addNewsForm);
                    loadNoticias(); // Recargar la lista
                } else {
                    showMessage(addNewsMessage, `Error al crear noticia: ${result.message || response.statusText}`, 'error');
                }
            } catch (error) {
                console.error('Error adding noticia:', error);
                showMessage(addNewsMessage, 'Ocurrió un error al conectar con la API.', 'error');
            }
        });
    }

    // --- CRUD: Editar Noticia ---

    // Delegación de eventos para botones "Editar" en la tabla
    if (newsTableBody) {
        newsTableBody.addEventListener('click', async (e) => {
            if (e.target.classList.contains('edit-news-button')) {
                const newsId = e.target.dataset.newsId;
                hideMessage(editNewsMessage);
                 hideMessage(document.getElementById('newsListMessage')); // Ocultar mensajes de lista

                try {
                    const response = await fetch(`${API_NOTICIAS_URL}/${newsId}`);
                    const noticia = await response.json();
                    const newsListMessageElement = document.getElementById('newsListMessage');

                    if (!response.ok) {
                        if(newsListMessageElement) showMessage(newsListMessageElement, `Error al obtener datos de la noticia para editar: ${noticia.message || response.statusText}`, 'error');
                         else console.error(`Error fetching noticia ${newsId}:`, noticia.message || response.statusText);
                         return;
                    }

                    // Rellenar el formulario de edición y mostrarlo
                    if(editNewsForm){
                         editNewsId.value = noticia.id;
                         editNewsTitulo.value = noticia.titulo;
                         editNewsDescripcion.value = noticia.descripcion;
                         // Formatear la fecha para el input datetime-local
                         editNewsFechaPublicacion.value = formatDateTimeLocal(noticia.fecha_publicacion);
                         editNewsUrlOrigen.value = noticia.url_origen;
                         editNewsImagen.value = noticia.imagen;
                         // Rellenar otros campos si aplican

                         if(editNewsSection) editNewsSection.style.display = 'block';
                    }

                } catch (error) {
                    console.error('Error fetching noticia for edit:', error);
                    const newsListMessageElement = document.getElementById('newsListMessage');
                    if(newsListMessageElement) showMessage(newsListMessageElement, 'Ocurrió un error al obtener los datos de la noticia para editar.', 'error');
                }
            }
        });
    }

    // Manejar el submit del formulario de edición
    if (editNewsForm) {
         editNewsForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideMessage(editNewsMessage);

            const newsId = editNewsId.value;
            const formData = new FormData(editNewsForm);
             const updateData = {};
             formData.forEach((value, key) => {
                  if (key === 'id') return; // No incluir el ID en el cuerpo

                  // Convertir el formato datetime-local a un formato compatible con tu backend si es necesario
                  // Ejemplo:
                  // if (key === 'fecha_publicacion' && value) {
                  //    updateData[key] = new Date(value).toISOString();
                  // } else if (value !== '') {
                  //    updateData[key] = value;
                  // }
                  // Si no necesita formateo especial:
                   if (value !== '') { updateData[key] = value; }

             });

             if (!newsId) {
                 showMessage(editNewsMessage, 'Error: ID de noticia para actualizar no encontrado.', 'error');
                 console.error('Edit form submitted without a news ID.');
                 return;
             }

             if (Object.keys(updateData).length === 0) {
                 showMessage(editNewsMessage, 'No hay campos para actualizar.', 'warning');
                 if(editNewsSection) editNewsSection.style.display = 'none';
                 return;
             }


            try {
                const response = await fetch(`${API_NOTICIAS_URL}/${newsId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(updateData)
                });
                 const result = await response.json();

                if (response.ok) {
                    showMessage(editNewsMessage, 'Noticia actualizada con éxito.', 'success');
                    if(editNewsSection) editNewsSection.style.display = 'none';
                    loadNoticias(); // Recargar la lista
                } else {
                    showMessage(editNewsMessage, `Error al actualizar noticia: ${result.message || response.statusText}`, 'error');
                }
            } catch (error) {
                 console.error('Error updating noticia:', error);
                 showMessage(editNewsMessage, 'Ocurrió un error al conectar con la API.', 'error');
            }
         });
    }

    // Botón cancelar edición
    if (cancelEditNewsButton && editNewsSection) {
        cancelEditNewsButton.addEventListener('click', () => {
            editNewsSection.style.display = 'none';
            hideMessage(editNewsMessage);
        });
    }

    // --- CRUD: Eliminar Noticia ---
     if (newsTableBody) {
        newsTableBody.addEventListener('click', async (e) => {
            if (e.target.classList.contains('delete-news-button')) {
                const newsId = e.target.dataset.newsId;
                const newsListMessageElement = document.getElementById('newsListMessage');

                if (!confirm(`¿Estás seguro de que quieres eliminar la noticia con ID ${newsId}?`)) {
                    return;
                }

                if(newsListMessageElement) hideMessage(newsListMessageElement);

                try {
                    const response = await fetch(`${API_NOTICIAS_URL}/${newsId}`, { method: 'DELETE' });
                    const result = await response.json();

                    if (response.ok) {
                        if(newsListMessageElement) showMessage(newsListMessageElement, `Noticia ${newsId} eliminada con éxito.`, 'success');
                        const row = e.target.closest('tr');
                        if (row) row.remove();
                        if (newsTableBody.children.length === 0) {
                             newsTableBody.innerHTML = '<tr><td colspan="6">No hay noticias registradas.</td></tr>'; // 6 columnas
                        }
                    } else {
                         if(newsListMessageElement) showMessage(newsListMessageElement, `Error al eliminar noticia ${newsId}: ${result.message || response.statusText}`, 'error');
                         else console.error(`Error deleting noticia ${newsId}:`, result.message || response.statusText);
                    }
                } catch (error) {
                    console.error('Error deleting noticia:', error);
                     if(newsListMessageElement){
                       showMessage(newsListMessageElement, 'Ocurrió un error al conectar con la API para eliminar la noticia.', 'error');
                     }
                }
            }
        });
     }

    // --- Fin DOMContentLoaded ---
});