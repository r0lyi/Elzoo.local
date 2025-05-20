// Archivo: /public/js/foro.js

// Este código se ejecutará cuando el script sea cargado dinámicamente por admin.js
console.log("foro.js cargado");

const API_FOROS_URL = '/api/v1/foros'; // !!! ASEGÚRATE DE QUE ESTA RUTA SEA CORRECTA !!!
// API para comentarios. Nota la estructura: /api/v1/foros/{foro_id}/comentarios para listar/añadir,
// y /api/v1/comentarios/{id} para show/update/delete individual.
// Usaremos un template string para la URL base de comentarios por foro.
const API_COMENTARIOS_BASE_URL = '/api/v1/foros'; // Para /api/v1/foros/{foro_id}/comentarios
const API_COMENTARIOS_INDIVIDUAL_URL = '/api/v1/comentarios'; // Para /api/v1/comentarios/{id}


// --- Funciones de Utilidad (Pueden estar en un archivo helpers.js compartido) ---
// Copia si no tienes un archivo compartido
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

// Helper para truncar contenido largo
function truncateContent(content, maxLength = 150) {
    if (!content) return '';
    const plainContent = content.replace(/<[^>]*>/g, ''); // Elimina etiquetas HTML si hay
    if (plainContent.length <= maxLength) return plainContent;
    return plainContent.substring(0, maxLength) + '...';
}


// --- CRUD: Listar Posts de Foro (Función de inicialización llamada por admin.js) ---

async function loadForos() {
    console.log("Cargando posts de foro...");
    // Referencias a elementos del DOM específicos de la sección foros (posts)
    const forumTableBody = document.getElementById('forumTableBody');
    const forumListMessage = document.getElementById('forumListMessage');
    const editForumSection = document.getElementById('editForumSection');

    if (!forumTableBody || !forumListMessage) {
         console.error('Elementos de tabla de foros o mensajes no encontrados al intentar cargar datos.');
         return;
    }

    hideMessage(forumListMessage);
    if(editForumSection) editForumSection.style.display = 'none'; // Ocultar form de edición al cargar la lista
    forumTableBody.innerHTML = ''; // Limpiar la tabla antes de cargar

    try {
        const response = await fetch(API_FOROS_URL); // API_FOROS_URL es GET /api/v1/foros
        const data = await response.json();

        if (!response.ok) {
            showMessage(forumListMessage, `Error al cargar posts de foro: ${data.message || response.statusText}`, 'error');
            return;
        }

        if (data.length === 0) {
            forumTableBody.innerHTML = '<tr><td colspan="6">No hay posts de foro registrados.</td></tr>'; // 6 columnas
            return;
        }

        // Renderizar posts de foro en la tabla
        data.forEach(post => {
            // Asumiendo que la API (Foro::findAllWithAuthor) devuelve algo como post.autor.nombre o post.autor_nombre
            const autorNombre = post.autor_nombre || post.autor?.nombre || `ID: ${post.autor_id}`;
            const fechaCreacion = post.fecha_creacion ? new Date(post.fecha_creacion).toLocaleString() : 'N/A';

            const row = forumTableBody.insertRow();
            row.innerHTML = `
                <td>${post.id}</td>
                <td>${post.titulo}</td>
                <td>${truncateContent(post.contenido)}</td>
                <td>${autorNombre}</td>
                <td>${fechaCreacion}</td>
                <td>
                     {# Botón para ver/gestionar comentarios #}
                     <button class="button-info view-comments-button" data-forum-id="${post.id}" data-post-title="${post.titulo}">Ver Comentarios</button>
                </td>
                <td>
                    <button class="button-warning edit-forum-button" data-forum-id="${post.id}">Editar</button>
                    <button class="button-danger delete-forum-button" data-forum-id="${post.id}">Eliminar</button>
                </td>
            `;
             // Añadir la columna para el conteo si la API lo devuelve
             // if (post.comment_count !== undefined) {
             //    const commentCountCell = row.insertCell(5); // Insertar antes de Acciones si Acciones es la última
             //    commentCountCell.textContent = post.comment_count;
             // } else {
             //    // Si la API no devuelve conteo, inserta una celda vacía o un placeholder
             //    row.insertCell(5).textContent = 'N/A';
             // }
        });

    } catch (error) {
        console.error('Error fetching foros:', error);
        showMessage(forumListMessage, 'Ocurrió un error al conectar con la API para cargar posts de foro.', 'error');
    }
}

// --- Exponer la función de inicialización globalmente ---
// El script admin.js la llamará después de cargar este archivo
window.loadForos = loadForos;


// --- EVENT LISTENERS PRINCIPALES (Posts de Foro y delegación para Comentarios) ---
document.addEventListener('DOMContentLoaded', () => {
     console.log("DOMContentLoaded en foro.js");

    // Referencias a elementos del DOM específicos de la sección foros
    const forumTableBody = document.getElementById('forumTableBody');
    const addForumForm = document.getElementById('addForumForm');
    const addForumMessage = document.getElementById('addForumMessage');

    const editForumSection = document.getElementById('editForumSection');
    const editForumForm = document.getElementById('editForumForm');
    const editForumId = document.getElementById('editForumId');
    const editForumTitulo = document.getElementById('editForumTitulo');
    const editForumContenido = document.getElementById('editForumContenido');
    const editForumMessage = document.getElementById('editForumMessage');
    const cancelEditForumButton = document.getElementById('cancelEditForumButton');

    // Referencias a elementos del DOM del MODAL DE COMENTARIOS
    const commentsModal = document.getElementById('commentsModal');
    const closeButton = commentsModal ? commentsModal.querySelector('.close-button') : null;
    const commentsModalTitle = document.getElementById('commentsModalTitle');
    const commentsModalPostTitle = document.getElementById('commentsModalPostTitle');
    const commentsModalForoId = document.getElementById('commentsModalForoId');
    const commentsListBody = document.getElementById('commentsListBody');
    const commentListMessage = document.getElementById('commentListMessage');

    // Formulario y elementos para AÑADIR COMENTARIO (dentro del modal)
    const addCommentForm = document.getElementById('addCommentForm');
    const newCommentAutorId = document.getElementById('newCommentAutorId'); // Campo autor_id en form añadir comentario
    const newCommentContenido = document.getElementById('newCommentContenido'); // Campo contenido en form añadir comentario
    const addCommentMessage = document.getElementById('addCommentMessage');

    // Formulario y elementos para EDITAR COMENTARIO (fuera o dentro del modal, inicialmente oculto)
    const editCommentSection = document.getElementById('editCommentSection'); // La sección/div que contiene el form
    const editCommentForm = document.getElementById('editCommentForm');
    const editCommentId = document.getElementById('editCommentId');
    const editCommentContenido = document.getElementById('editCommentContenido'); // Campo contenido en form editar comentario
    const editCommentMessage = document.getElementById('editCommentMessage');
    const cancelEditCommentButton = document.getElementById('cancelEditCommentButton');


    // --- Modal Functions ---
    function openCommentsModal(foroId, postTitle) {
        if (commentsModal) {
            commentsModal.style.display = 'block';
             if(commentsModalPostTitle) commentsModalPostTitle.textContent = postTitle || 'Cargando...';
             if(commentsModalForoId) commentsModalForoId.value = foroId; // Guardar el foroId actual
             hideMessage(commentListMessage); // Ocultar mensajes de la lista de comentarios
             hideMessage(addCommentMessage); // Ocultar mensajes del form añadir comentario
             hideMessage(editCommentMessage); // Ocultar mensajes del form editar comentario
             if(editCommentSection) editCommentSection.style.display = 'none'; // Ocultar form editar
             if(addCommentForm) clearForm(addCommentForm); // Limpiar form añadir
            loadComments(foroId); // Cargar los comentarios para este foro
        }
    }

    function closeCommentsModal() {
        if (commentsModal) {
            commentsModal.style.display = 'none';
             if(commentsModalForoId) commentsModalForoId.value = ''; // Limpiar foroId guardado
             if(commentsListBody) commentsListBody.innerHTML = ''; // Limpiar lista de comentarios
             hideMessage(commentListMessage);
             hideMessage(addCommentMessage);
             hideMessage(editCommentMessage);
             if(editCommentSection) editCommentSection.style.display = 'none';
        }
    }

    // --- CRUD: Listar Comentarios por Foro ---
    async function loadComments(foroId) {
        console.log(`Cargando comentarios para el foro ID: ${foroId}`);
         if (!commentsListBody || !commentListMessage) {
              console.error('Elementos de lista de comentarios no encontrados.');
              return;
         }

        hideMessage(commentListMessage);
        commentsListBody.innerHTML = ''; // Limpiar la lista de comentarios

        try {
            // API_COMENTARIOS_BASE_URL es /api/v1/foros -> construimos /api/v1/foros/{foro_id}/comentarios
            const response = await fetch(`${API_COMENTARIOS_BASE_URL}/${foroId}/comentarios`);
            const data = await response.json(); // Asumiendo que devuelve un array de comentarios

            if (!response.ok) {
                showMessage(commentListMessage, `Error al cargar comentarios: ${data.message || response.statusText}`, 'error');
                return;
            }

            if (data.length === 0) {
                commentsListBody.innerHTML = '<li>No hay comentarios para este post.</li>';
                return;
            }

            // Renderizar comentarios
            data.forEach(comment => {
                 // Asumiendo que ComentarioForo::findByForoIdWithAuthor devuelve comment.autor.nombre o comment.autor_nombre
                const autorNombre = comment.autor_nombre || comment.autor?.nombre || `ID: ${comment.autor_id}`;
                const fechaCreacion = comment.fecha_creacion ? new Date(comment.fecha_creacion).toLocaleString() : 'N/A'; // Asumiendo campo 'fecha_creacion'

                const li = document.createElement('li');
                li.innerHTML = `
                    <p><strong>${autorNombre}</strong> (${fechaCreacion}): ${comment.contenido}</p>
                    <button class="button-warning edit-comment-button" data-comment-id="${comment.id}">Editar</button>
                    <button class="button-danger delete-comment-button" data-comment-id="${comment.id}">Eliminar</button>
                `;
                commentsListBody.appendChild(li);
            });

        } catch (error) {
            console.error('Error fetching comments:', error);
            showMessage(commentListMessage, 'Ocurrió un error al conectar con la API para cargar comentarios.', 'error');
        }
    }


    // --- Event Listeners para Posts de Foro ---

    // CRUD: Añadir Post de Foro (Mismo código que antes)
    if (addForumForm) { /* ... (código submit form añadir) ... */
        addForumForm.addEventListener('submit', async (e) => {
            e.preventDefault(); hideMessage(addForumMessage);
            const formData = new FormData(addForumForm);
            const forumData = Object.fromEntries(formData.entries());
            forumData.autor_id = parseInt(forumData.autor_id, 10);
            if (isNaN(forumData.autor_id) || forumData.autor_id < 1) {
                 showMessage(addForumMessage, 'ID de Autor inválido.', 'error'); return;
            }
            try { /* ... fetch POST ... */
                 const response = await fetch(API_FOROS_URL, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(forumData) });
                const result = await response.json();
                if (response.status === 201) {
                    showMessage(addForumMessage, 'Post de foro creado con éxito.', 'success'); clearForm(addForumForm); loadForos(); // Recargar la lista principal
                } else { showMessage(addForumMessage, `Error al crear post de foro: ${result.message || response.statusText}`, 'error'); }
            } catch (error) { console.error('Error adding forum post:', error); showMessage(addForumMessage, 'Ocurrió un error al conectar con la API para crear el post.', 'error'); }
        });
    }

    // CRUD: Editar Post de Foro (Delegación y Submit) (Mismo código que antes)
    if (forumTableBody) { /* ... (código delegación click editar) ... */
         forumTableBody.addEventListener('click', async (e) => {
            if (e.target.classList.contains('edit-forum-button')) {
                const forumId = e.target.dataset.forumId; hideMessage(editForumMessage); hideMessage(document.getElementById('forumListMessage'));
                try { /* ... fetch GET /foros/{id} ... */
                    const response = await fetch(`${API_FOROS_URL}/${forumId}`); const post = await response.json();
                    const forumListMessageElement = document.getElementById('forumListMessage');
                    if (!response.ok) {
                         if(forumListMessageElement) showMessage(forumListMessageElement, `Error al obtener datos del post de foro para editar: ${post.message || response.statusText}`, 'error');
                         else console.error(`Error fetching post ${forumId}:`, post.message || response.statusText); return;
                    }
                    if(editForumForm){ editForumId.value = post.id; editForumTitulo.value = post.titulo; editForumContenido.value = post.contenido; if(editForumSection) editForumSection.style.display = 'block'; }
                } catch (error) { console.error('Error fetching forum post for edit:', error); const forumListMessageElement = document.getElementById('forumListMessage'); if(forumListMessageElement) showMessage(forumListMessageElement, 'Ocurrió un error al obtener los datos del post de foro para editar.', 'error'); }
            }
        });
    }

    if (editForumForm) { /* ... (código submit form editar) ... */
         editForumForm.addEventListener('submit', async (e) => {
            e.preventDefault(); hideMessage(editForumMessage); const forumId = editForumId.value; const formData = new FormData(editForumForm);
             const updateData = { titulo: formData.get('titulo'), contenido: formData.get('contenido') };
             if (updateData.titulo.trim() === '') delete updateData.titulo; if (updateData.contenido.trim() === '') delete updateData.contenido;
             if (!forumId) { showMessage(editForumMessage, 'Error: ID de post de foro para actualizar no encontrado.', 'error'); return; }
             if (Object.keys(updateData).length === 0) { showMessage(editForumMessage, 'No hay campos para actualizar.', 'warning'); if(editForumSection) editForumSection.style.display = 'none'; return; }
            try { /* ... fetch PUT /foros/{id} ... */
                 const response = await fetch(`${API_FOROS_URL}/${forumId}`, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(updateData) });
                 const result = await response.json();
                if (response.ok) {
                    showMessage(editForumMessage, 'Post de foro actualizado con éxito.', 'success'); if(editForumSection) editForumSection.style.display = 'none'; loadForos(); // Recargar la lista principal
                } else { showMessage(editForumMessage, `Error al actualizar post de foro: ${result.message || response.statusText}`, 'error'); }
            } catch (error) { console.error('Error updating forum post:', error); showMessage(editForumMessage, 'Ocurrió un error al conectar con la API para actualizar el post.', 'error'); }
         });
    }

    if (cancelEditForumButton && editForumSection) { /* ... (código cancelar editar) ... */
        cancelEditForumButton.addEventListener('click', () => { editForumSection.style.display = 'none'; hideMessage(editForumMessage); });
    }

    // CRUD: Eliminar Post de Foro (Delegación) (Mismo código que antes)
     if (forumTableBody) { /* ... (código delegación click eliminar) ... */
        forumTableBody.addEventListener('click', async (e) => {
            if (e.target.classList.contains('delete-forum-button')) {
                const forumId = e.target.dataset.forumId; const forumListMessageElement = document.getElementById('forumListMessage');
                if (!confirm(`¿Estás seguro de que quieres eliminar el post de foro con ID ${forumId} (y sus comentarios asociados)? Esta acción no se puede deshacer.`)) return;
                if(forumListMessageElement) hideMessage(forumListMessageElement);
                try { /* ... fetch DELETE /foros/{id} ... */
                    const response = await fetch(`${API_FOROS_URL}/${forumId}`, { method: 'DELETE' });
                    const result = await response.json();
                    if (response.ok) {
                        if(forumListMessageElement) showMessage(forumListMessageElement, `Post de foro ${forumId} eliminado con éxito.`, 'success');
                        const row = e.target.closest('tr'); if (row) row.remove();
                        if (forumTableBody.children.length === 0) { forumTableBody.innerHTML = '<tr><td colspan="6">No hay posts de foro registrados.</td></tr>'; }
                    } else {
                         if(forumListMessageElement) showMessage(forumListMessageElement, `Error al eliminar post de foro ${forumId}: ${result.message || response.statusText}`, 'error');
                         else console.error(`Error deleting forum post ${forumId}:`, result.message || response.statusText);
                    }
                } catch (error) { console.error('Error deleting forum post:', error); if(forumListMessageElement) showMessage(forumListMessageElement, 'Ocurrió un error al conectar con la API para eliminar el post.', 'error'); }
            }
        });
     }


    // --- Event Listeners para Modal de Comentarios ---

    // Delegación de evento para abrir el modal al hacer clic en "Ver Comentarios" en la tabla principal
    if (forumTableBody) {
        forumTableBody.addEventListener('click', async (e) => {
            if (e.target.classList.contains('view-comments-button')) {
                const foroId = e.target.dataset.forumId;
                const postTitle = e.target.dataset.postTitle;
                openCommentsModal(foroId, postTitle); // Abrir el modal y cargar comentarios
            }
        });
    }

    // Cerrar el modal al hacer clic en el botón de cerrar (X)
    if (closeButton) {
        closeButton.addEventListener('click', closeCommentsModal);
    }

    // Cerrar el modal al hacer clic fuera del contenido del modal
    if (commentsModal) {
        window.addEventListener('click', (event) => {
            if (event.target === commentsModal) {
                closeCommentsModal();
            }
        });
    }


    // --- CRUD: Añadir Comentario (dentro del modal) ---
    if (addCommentForm) {
         addCommentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideMessage(addCommentMessage);

            const foroId = commentsModalForoId.value; // Obtener el foroId del campo oculto en el modal
            if (!foroId) {
                 showMessage(addCommentMessage, 'Error: ID de foro no encontrado para añadir comentario.', 'error');
                 return;
            }

            const formData = new FormData(addCommentForm);
            const commentData = Object.fromEntries(formData.entries());

             // Asegurarse de que autor_id es numérico
            commentData.autor_id = parseInt(commentData.autor_id, 10);
            if (isNaN(commentData.autor_id) || commentData.autor_id < 1) {
                 showMessage(addCommentMessage, 'ID de Autor inválido para el comentario.', 'error');
                 return;
            }

            // API_COMENTARIOS_BASE_URL es /api/v1/foros -> construimos /api/v1/foros/{foro_id}/comentarios para POST
            try {
                const response = await fetch(`${API_COMENTARIOS_BASE_URL}/${foroId}/comentarios`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(commentData) // forum_id ya va en la URL
                });
                const result = await response.json();

                if (response.status === 201) {
                    showMessage(addCommentMessage, 'Comentario añadido con éxito.', 'success');
                    clearForm(addCommentForm);
                    loadComments(foroId); // Recargar la lista de comentarios del modal
                } else {
                     // La API devuelve 400 si falta campo, 404 si autor_id no existe, etc.
                    showMessage(addCommentMessage, `Error al añadir comentario: ${result.message || response.statusText}`, 'error');
                }
            } catch (error) {
                console.error('Error adding comment:', error);
                showMessage(addCommentMessage, 'Ocurrió un error al conectar con la API para añadir el comentario.', 'error');
            }
         });
    }

    // --- CRUD: Editar Comentario ---

    // Delegación de eventos para botones "Editar" dentro de la lista de comentarios del modal
    if (commentsListBody) {
         commentsListBody.addEventListener('click', async (e) => {
            if (e.target.classList.contains('edit-comment-button')) {
                const commentId = e.target.dataset.commentId;
                hideMessage(editCommentMessage);
                 hideMessage(commentListMessage); // Ocultar mensajes de la lista

                try {
                    // API_COMENTARIOS_INDIVIDUAL_URL es /api/v1/comentarios -> usamos /api/v1/comentarios/{id} para GET
                    const response = await fetch(`${API_COMENTARIOS_INDIVIDUAL_URL}/${commentId}`);
                    const comment = await response.json(); // Asumiendo que devuelve { id, foro_id, autor_id, contenido, ... }

                    const commentListMessageElement = document.getElementById('commentListMessage');

                    if (!response.ok) {
                         if(commentListMessageElement) showMessage(commentListMessageElement, `Error al obtener datos del comentario para editar: ${comment.message || response.statusText}`, 'error');
                         else console.error(`Error fetching comment ${commentId}:`, comment.message || response.statusText); return;
                    }

                    // Rellenar el formulario de edición y mostrarlo
                    if(editCommentForm){
                         editCommentId.value = comment.id;
                         editCommentContenido.value = comment.contenido;
                         // No rellenamos foro_id o autor_id ya que la API PUT individual no los actualiza

                         if(editCommentSection) editCommentSection.style.display = 'block'; // Mostrar la sección de edición de comentario
                    }

                } catch (error) {
                    console.error('Error fetching comment for edit:', error);
                    const commentListMessageElement = document.getElementById('commentListMessage');
                    if(commentListMessageElement) showMessage(commentListMessageElement, 'Ocurrió un error al obtener los datos del comentario para editar.', 'error');
                }
            }
        });
    }

    // Manejar el submit del formulario de edición de comentario
    if (editCommentForm) {
         editCommentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideMessage(editCommentMessage);

            const commentId = editCommentId.value;
            const foroId = commentsModalForoId.value; // Necesitamos el foroId para recargar la lista después
            if (!commentId || !foroId) {
                 showMessage(editCommentMessage, 'Error: IDs necesarios para actualizar comentario no encontrados.', 'error');
                 return;
            }

            const formData = new FormData(editCommentForm);
             const updateData = {
                 contenido: formData.get('contenido')
                 // Solo permitimos actualizar contenido según la API
             };

             if (updateData.contenido.trim() === '') {
                 showMessage(editCommentMessage, 'El contenido del comentario no puede estar vacío.', 'warning');
                  return;
             }

            try {
                // API_COMENTARIOS_INDIVIDUAL_URL es /api/v1/comentarios -> usamos /api/v1/comentarios/{id} para PUT
                const response = await fetch(`${API_COMENTARIOS_INDIVIDUAL_URL}/${commentId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(updateData)
                });
                 const result = await response.json();

                if (response.ok) { // 200 OK
                    showMessage(editCommentMessage, 'Comentario actualizado con éxito.', 'success');
                    if(editCommentSection) editCommentSection.style.display = 'none';
                    loadComments(foroId); // Recargar la lista de comentarios del modal
                } else {
                    showMessage(editCommentMessage, `Error al actualizar comentario: ${result.message || response.statusText}`, 'error');
                }

            } catch (error) {
                 console.error('Error updating comment:', error);
                 showMessage(editCommentMessage, 'Ocurrió un error al conectar con la API para actualizar el comentario.', 'error');
            }
         });
    }

    // Botón cancelar edición de comentario
    if (cancelEditCommentButton && editCommentSection) {
        cancelEditCommentButton.addEventListener('click', () => {
            editCommentSection.style.display = 'none';
            hideMessage(editCommentMessage);
        });
    }


    // --- CRUD: Eliminar Comentario ---

    // Delegación de eventos para botones "Eliminar" dentro de la lista de comentarios del modal
     if (commentsListBody) {
        commentsListBody.addEventListener('click', async (e) => {
            if (e.target.classList.contains('delete-comment-button')) {
                const commentId = e.target.dataset.commentId;
                 const commentListMessageElement = document.getElementById('commentListMessage');
                 const foroId = commentsModalForoId.value; // Necesitamos el foroId para recargar la lista después

                if (!confirm(`¿Estás seguro de que quieres eliminar el comentario con ID ${commentId}?`)) {
                    return;
                }

                if(commentListMessageElement) hideMessage(commentListMessageElement);
                if(editCommentSection) editCommentSection.style.display = 'none'; // Ocultar form de edición si estaba abierto para este comentario

                try {
                    // API_COMENTARIOS_INDIVIDUAL_URL es /api/v1/comentarios -> usamos /api/v1/comentarios/{id} para DELETE
                    const response = await fetch(`${API_COMENTARIOS_INDIVIDUAL_URL}/${commentId}`, { method: 'DELETE' });
                    const result = await response.json();

                    if (response.ok) { // 200 OK
                        if(commentListMessageElement) showMessage(commentListMessageElement, `Comentario ${commentId} eliminado con éxito.`, 'success');
                        // Eliminar el elemento de lista (<li>) del DOM
                        const listItem = e.target.closest('li');
                        if (listItem) {
                            listItem.remove();
                        }
                        // Si la lista queda vacía
                        if (commentsListBody.children.length === 0) {
                             commentsListBody.innerHTML = '<li>No hay comentarios para este post.</li>';
                        }
                        // Opcional: Recargar la lista principal de foros para actualizar el conteo de comentarios si la API index lo soporta
                         // loadForos();

                    } else {
                         if(commentListMessageElement) showMessage(commentListMessageElement, `Error al eliminar comentario ${commentId}: ${result.message || response.statusText}`, 'error');
                         else console.error(`Error deleting comment ${commentId}:`, result.message || response.statusText);
                    }

                } catch (error) {
                    console.error('Error deleting comment:', error);
                     if(commentListMessageElement){
                       showMessage(commentListMessageElement, 'Ocurrió un error al conectar con la API para eliminar el comentario.', 'error');
                     }
                }
            }
        });
     }


    // --- Fin DOMContentLoaded ---
});