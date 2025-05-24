document.addEventListener('DOMContentLoaded', () => {
    const API_URL = '/api/v1/foros'; // Tu endpoint base de la API para foros
    const foroForm = document.getElementById('foro-form');
    const foroIdInput = document.getElementById('foro-id');
    const tituloInput = document.getElementById('titulo');
    const contenidoInput = document.getElementById('contenido');
    const autorIdInput = document.getElementById('autor_id');
    const fechaCreacionInput = document.getElementById('fecha_creacion');
    const saveForoBtn = document.getElementById('save-foro-btn');
    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    const formTitleText = document.getElementById('form-title-text');
    const autorIdContainer = document.getElementById('autor-id-container'); // Necesario para mostrar/ocultar

    const forosTableBody = document.querySelector('#foros-table tbody');
    const noForosMessage = document.getElementById('no-foros-message');
    const messageArea = document.getElementById('message-area');

    // Elementos del buscador (nuevos)
    const searchInput = document.getElementById('search-input');

    // Variables para almacenar todos los foros cargados inicialmente
    let allForos = [];
    let currentEditForoId = null;

    // --- Funciones de Utilidad ---

    function showMessage(message, type) {
        messageArea.textContent = message;
        messageArea.className = `alert alert-${type}`;
        messageArea.classList.remove('d-none');
        setTimeout(() => {
            messageArea.classList.add('d-none');
        }, 5000);
    }

    function resetForm() {
        foroForm.reset();
        foroIdInput.value = '';
        currentEditForoId = null;
        formTitleText.textContent = 'Editar Post Existente';
        saveForoBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Actualizar Post';
        saveForoBtn.classList.remove('btn-primary');
        saveForoBtn.classList.add('btn-warning');
        cancelEditBtn.classList.add('d-none');
        autorIdInput.setAttribute('readonly', 'true');
        autorIdContainer.classList.add('d-none'); // Ocultar el contenedor del autor
    }

    function formatDisplayDate(isoDateString) {
        if (!isoDateString) return 'N/A';
        const date = new Date(isoDateString);
        return date.toLocaleString('es-ES', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function createForoRow(foro) {
        const displayFecha = formatDisplayDate(foro.fecha_creacion);
        const autorNombre = foro.autor_nombre ? foro.autor_nombre : `ID: ${foro.autor_id}`;

        return `
            <tr>
                <td>${foro.id}</td>
                <td>${foro.titulo}</td>
                <td>${autorNombre}</td>
                <td>${displayFecha}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-info view-details-btn me-2" data-id="${foro.id}" data-bs-toggle="modal" data-bs-target="#foroDetailsModal">
                        <i class="fas fa-eye"></i> Ver
                    </button>
                    <button class="btn btn-sm btn-warning edit-btn me-2" data-id="${foro.id}">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="${foro.id}">
                        <i class="fas fa-trash-alt"></i> Eliminar
                    </button>
                </td>
            </tr>
        `;
    }

    // --- Renderizado de Foros en la Tabla ---
    function renderForos(forosToRender) {
        forosTableBody.innerHTML = '';
        if (forosToRender.length > 0) {
            forosToRender.forEach(foro => {
                forosTableBody.innerHTML += createForoRow(foro);
            });
            noForosMessage.classList.add('d-none');
        } else {
            noForosMessage.classList.remove('d-none');
        }
        addEventListenersToButtons();
    }

    // --- Operaciones API ---

    async function fetchAllForos() {
        try {
            const response = await fetch(API_URL); // Petición sin filtros para obtener todo
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const foros = await response.json();
            allForos = foros; // Almacena todos los foros
            renderForos(allForos); // Renderiza inicialmente todos los foros
        } catch (error) {
            console.error('Error al cargar todos los foros:', error);
            showMessage('Error al cargar los posts del foro.', 'danger');
            forosTableBody.innerHTML = '';
            noForosMessage.classList.remove('d-none');
        }
    }

    async function saveForo(foroData, id) {
        if (!id) {
            showMessage('Error: Solo se permite la actualización de foros desde esta interfaz de administración.', 'danger');
            return;
        }

        try {
            const response = await fetch(`${API_URL}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(foroData)
            });

            const result = await response.json();

            if (!response.ok) {
                const errorMessage = result.message || 'Error desconocido al actualizar el foro.';
                throw new Error(`API error: ${errorMessage}`);
            }

            showMessage(`Foro actualizado con éxito.`, 'success');
            resetForm();
            fetchAllForos(); // Vuelve a cargar todos los foros después de actualizar
        } catch (error) {
            console.error('Error al actualizar el foro:', error);
            showMessage(error.message, 'danger');
        }
    }

    async function deleteForo(id) {
        if (!confirm('¿Estás seguro de que quieres eliminar este post y todos sus comentarios? Esta acción es irreversible.')) {
            return;
        }

        try {
            const response = await fetch(`${API_URL}/${id}`, {
                method: 'DELETE'
            });

            const result = await response.json();

            if (!response.ok) {
                const errorMessage = result.message || 'Error desconocido al eliminar el foro.';
                throw new Error(`API error: ${errorMessage}`);
            }

            showMessage('Post y sus comentarios asociados eliminados con éxito.', 'success');
            fetchAllForos(); // Vuelve a cargar todos los foros después de eliminar
            resetForm();
        } catch (error) {
            console.error('Error al eliminar el foro:', error);
            showMessage(error.message, 'danger');
        }
    }

    // --- Manejadores de Eventos de Posts ---

    foroForm.addEventListener('submit', (e) => {
        e.preventDefault();

        if (!currentEditForoId) {
            showMessage('No se puede guardar un post sin un ID para editar.', 'danger');
            return;
        }

        const foroData = {
            titulo: tituloInput.value.trim(),
            contenido: contenidoInput.value.trim(),
        };

        if (foroData.titulo === '' || foroData.contenido === '') {
            showMessage('Por favor, completa el título y el contenido del post.', 'warning');
            return;
        }

        saveForo(foroData, currentEditForoId);
    });

    cancelEditBtn.addEventListener('click', resetForm);

    function addEventListenersToButtons() {
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.removeEventListener('click', handleEditClick); // Eliminar listeners previos
            button.addEventListener('click', handleEditClick);
        });

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.removeEventListener('click', handleDeleteClick); // Eliminar listeners previos
            button.addEventListener('click', handleDeleteClick);
        });

        document.querySelectorAll('.view-details-btn').forEach(button => {
            button.removeEventListener('click', handleViewDetailsClick); // Eliminar listeners previos
            button.addEventListener('click', handleViewDetailsClick);
        });
    }

    async function handleEditClick(event) {
        const id = event.target.dataset.id;
        try {
            const response = await fetch(`${API_URL}/${id}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const foro = await response.json();

            foroIdInput.value = foro.id;
            tituloInput.value = foro.titulo;
            contenidoInput.value = foro.contenido;
            autorIdInput.value = foro.autor_id;
            fechaCreacionInput.value = formatDisplayDate(foro.fecha_creacion);

            currentEditForoId = foro.id;
            formTitleText.textContent = `Editando Post (ID: ${foro.id})`;
            saveForoBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Actualizar Post';
            saveForoBtn.classList.remove('btn-primary');
            saveForoBtn.classList.add('btn-warning');
            cancelEditBtn.classList.remove('d-none');
            autorIdContainer.classList.remove('d-none'); // Mostrar el contenedor del autor

        } catch (error) {
            console.error('Error al cargar el post para edición:', error);
            showMessage('Error al cargar el post para edición.', 'danger');
        }
    }

    function handleDeleteClick(event) {
        const id = event.target.dataset.id;
        deleteForo(id);
    }

    // --- Manejo del Modal de Detalles y Comentarios ---

    // Elementos del Modal de Detalles
    const foroDetailsModal = new bootstrap.Modal(document.getElementById('foroDetailsModal'));
    const modalForoTitulo = document.getElementById('modal-foro-titulo');
    const modalForoAutorNombre = document.getElementById('modal-foro-autor-nombre');
    const modalForoAutorId = document.getElementById('modal-foro-autor-id');
    const modalForoFechaCreacion = document.getElementById('modal-foro-fecha-creacion');
    const modalForoId = document.getElementById('modal-foro-id');
    const modalForoContenido = document.getElementById('modal-foro-contenido');
    const commentsListGroup = document.getElementById('comments-list-group');
    const noCommentsMessage = document.getElementById('no-comments-message');

    // Elementos del Formulario de Comentarios en el Modal
    const commentForm = document.getElementById('comment-form');
    const commentIdInput = document.getElementById('comment-id');
    const commentForoIdInput = document.getElementById('comment-foro-id');
    const commentAutorIdInput = document.getElementById('comment-autor-id');
    const commentContenidoInput = document.getElementById('comment-contenido');
    const saveCommentBtn = document.getElementById('save-comment-btn');
    const cancelCommentEditBtn = document.getElementById('cancel-comment-edit-btn');
    const commentFormTitleText = document.getElementById('comment-form-title-text');

    let currentEditCommentId = null; // Para el ID del comentario que se está editando

    async function handleViewDetailsClick(event) {
        const foroId = event.target.dataset.id;
        try {
            const responseForo = await fetch(`${API_URL}/${foroId}`);
            if (!responseForo.ok) {
                throw new Error(`HTTP error! status: ${responseForo.status}`);
            }
            const foro = await responseForo.json();

            modalForoTitulo.textContent = foro.titulo;
            modalForoAutorNombre.textContent = foro.autor_nombre || 'N/A';
            modalForoAutorId.textContent = foro.autor_id;
            modalForoFechaCreacion.textContent = formatDisplayDate(foro.fecha_creacion);
            modalForoId.textContent = foro.id;
            modalForoContenido.innerHTML = foro.contenido.replace(/\n/g, '<br>'); // Permite saltos de línea

            // Cargar comentarios
            const API_COMMENTS_URL = `/api/v1/foros/${foroId}/comentarios`;
            const responseComments = await fetch(API_COMMENTS_URL);
            if (!responseComments.ok) {
                throw new Error(`HTTP error! status: ${responseComments.status}`);
            }
            const comentarios = await responseComments.json();

            renderComments(comentarios); // Función para renderizar comentarios
            resetCommentForm(); // Limpiar formulario de comentarios al abrir modal
            commentForoIdInput.value = foroId; // Asegurarse de que el foro_id esté en el formulario de comentario
            foroDetailsModal.show();

        } catch (error) {
            console.error('Error al cargar los detalles del post o comentarios:', error);
            showMessage('Error al cargar los detalles del post o comentarios.', 'danger');
            foroDetailsModal.hide(); // Ocultar modal si hay error
        }
    }

    function renderComments(comentariosToRender) {
        commentsListGroup.innerHTML = '';
        if (comentariosToRender.length > 0) {
            comentariosToRender.forEach(comentario => {
                commentsListGroup.innerHTML += `
                    <li class="list-group-item d-flex justify-content-between align-items-start mb-2 shadow-sm rounded">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold">Comentario ID: ${comentario.id} - Autor: ${comentario.autor_nombre || `ID: ${comentario.autor_id}`}</div>
                            <small class="text-muted">${formatDisplayDate(comentario.fecha_creacion)}</small>
                            <p class="mb-1 mt-2">${comentario.contenido}</p>
                        </div>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-warning btn-sm edit-comment-btn me-2" data-id="${comentario.id}" data-foro-id="${comentario.foro_id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm delete-comment-btn" data-id="${comentario.id}" data-foro-id="${comentario.foro_id}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </li>
                `;
            });
            noCommentsMessage.classList.add('d-none');
        } else {
            noCommentsMessage.classList.remove('d-none');
        }
        addEventListenersToCommentButtons();
    }


    function addEventListenersToCommentButtons() {
        document.querySelectorAll('.edit-comment-btn').forEach(button => {
            button.removeEventListener('click', handleEditCommentClick);
            button.addEventListener('click', handleEditCommentClick);
        });

        document.querySelectorAll('.delete-comment-btn').forEach(button => {
            button.removeEventListener('click', handleDeleteCommentClick);
            button.addEventListener('click', handleDeleteCommentClick);
        });
    }

    async function handleEditCommentClick(event) {
        const commentId = event.target.closest('button').dataset.id; // Usar closest para asegurar el botón
        const foroId = event.target.closest('button').dataset.foroId;
        const API_SINGLE_COMMENT_URL = `/api/v1/foros/${foroId}/comentarios/${commentId}`;

        try {
            const response = await fetch(API_SINGLE_COMMENT_URL);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const comentario = await response.json();

            commentIdInput.value = comentario.id;
            commentForoIdInput.value = comentario.foro_id;
            commentAutorIdInput.value = comentario.autor_id;
            commentContenidoInput.value = comentario.contenido;

            currentEditCommentId = comentario.id;
            commentFormTitleText.textContent = `Editando Comentario (ID: ${comentario.id})`;
            saveCommentBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Actualizar Comentario';
            saveCommentBtn.classList.remove('btn-primary');
            saveCommentBtn.classList.add('btn-warning');
            cancelCommentEditBtn.classList.remove('d-none');

        } catch (error) {
            console.error('Error al cargar el comentario para edición:', error);
            showMessage('Error al cargar el comentario para edición.', 'danger');
        }
    }

    async function handleDeleteCommentClick(event) {
        const commentId = event.target.closest('button').dataset.id; // Usar closest para asegurar el botón
        const foroId = event.target.closest('button').dataset.foroId;
        const API_DELETE_COMMENT_URL = `/api/v1/foros/${foroId}/comentarios/${commentId}`;

        if (!confirm('¿Estás seguro de que quieres eliminar este comentario?')) {
            return;
        }

        try {
            const response = await fetch(API_DELETE_COMMENT_URL, {
                method: 'DELETE'
            });

            const result = await response.json();

            if (!response.ok) {
                const errorMessage = result.message || 'Error desconocido al eliminar el comentario.';
                throw new Error(`API error: ${errorMessage}`);
            }

            showMessage('Comentario eliminado con éxito.', 'success');
            // Recargar los comentarios del modal actual
            handleViewDetailsClick({ target: { dataset: { id: foroId } } });
            resetCommentForm();
        } catch (error) {
            console.error('Error al eliminar el comentario:', error);
            showMessage(error.message, 'danger');
        }
    }

    commentForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const commentId = commentIdInput.value;
        const foroId = commentForoIdInput.value;
        const autorId = commentAutorIdInput.value;
        const contenido = commentContenidoInput.value.trim();

        if (!commentId || !foroId || !autorId || contenido === '') {
            showMessage('Faltan datos para actualizar el comentario.', 'warning');
            return;
        }

        const API_UPDATE_COMMENT_URL = `/api/v1/foros/${foroId}/comentarios/${commentId}`;

        try {
            const response = await fetch(API_UPDATE_COMMENT_URL, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    autor_id: autorId,
                    contenido: contenido
                })
            });

            const result = await response.json();

            if (!response.ok) {
                const errorMessage = result.message || 'Error desconocido al actualizar el comentario.';
                throw new Error(`API error: ${errorMessage}`);
            }

            showMessage('Comentario actualizado con éxito.', 'success');
            // Recargar los comentarios del modal actual
            handleViewDetailsClick({ target: { dataset: { id: foroId } } });
            resetCommentForm();
        } catch (error) {
            console.error('Error al actualizar el comentario:', error);
            showMessage(error.message, 'danger');
        }
    });

    cancelCommentEditBtn.addEventListener('click', resetCommentForm);

    function resetCommentForm() {
        commentForm.reset();
        commentIdInput.value = '';
        currentEditCommentId = null;
        commentFormTitleText.textContent = 'Editar Comentario';
        saveCommentBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Actualizar Comentario';
        saveCommentBtn.classList.remove('btn-primary');
        saveCommentBtn.classList.add('btn-warning');
        cancelCommentEditBtn.classList.add('d-none'); // Ocultar el botón cancelar por defecto
        commentAutorIdInput.setAttribute('readonly', 'true'); // Asegurar que el ID del autor sea de solo lectura
    }

    // --- Funcionalidad de Búsqueda (JavaScript en el Cliente) ---

    // Función para realizar la búsqueda
    const performSearch = () => {
        const searchTerm = searchInput.value.toLowerCase().trim();

        if (searchTerm === '') {
            renderForos(allForos); // Si no hay término de búsqueda, muestra todos los foros
            return;
        }

        const filteredForos = allForos.filter(foro => {
            const tituloMatch = foro.titulo.toLowerCase().includes(searchTerm);
            const autorMatch = foro.autor_nombre && foro.autor_nombre.toLowerCase().includes(searchTerm); // Usa autor_nombre
            return tituloMatch || autorMatch;
        });
        renderForos(filteredForos);
    };

    // Escuchar cambios en el input de búsqueda
    searchInput.addEventListener('input', performSearch);


    // --- Inicialización ---
    // Cargar todos los foros al iniciar la página
    fetchAllForos();
});