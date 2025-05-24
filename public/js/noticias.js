document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM completamente cargado. Iniciando script noticias.js');

    const API_URL = '/api/v1/noticias'; // Tu endpoint base de la API
    const noticiaForm = document.getElementById('noticia-form');
    const noticiaIdInput = document.getElementById('noticia-id');
    const tituloInput = document.getElementById('titulo');
    const descripcionInput = document.getElementById('descripcion');
    const fechaPublicacionInput = document.getElementById('fecha_publicacion');
    const urlOrigenInput = document.getElementById('url_origen');
    const imagenInput = document.getElementById('imagen');
    const saveNoticiaBtn = document.getElementById('save-noticia-btn');
    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    const formTitleText = document.getElementById('form-title-text');

    const noticiasTableBody = document.querySelector('#noticias-table tbody');
    const noNoticiasMessage = document.getElementById('no-noticias-message');
    const messageArea = document.getElementById('message-area');

    // Elementos del buscador
    const searchInput = document.getElementById('search-input');
    // Verificación inicial del input de búsqueda
    if (!searchInput) {
        console.error("ERROR CRÍTICO: Elemento con ID 'search-input' no encontrado en el DOM. El buscador no funcionará.");
        showMessage("Error: El campo de búsqueda no se encontró. Contacta al administrador.", "danger");
    } else {
        console.log("Elemento 'search-input' encontrado correctamente.");
    }

    let allNoticias = []; // Almacena todas las noticias cargadas inicialmente
    let currentEditNoticiaId = null;

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
        noticiaForm.reset();
        noticiaIdInput.value = '';
        currentEditNoticiaId = null;
        formTitleText.textContent = 'Crear Nueva Noticia';
        saveNoticiaBtn.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Noticia';
        saveNoticiaBtn.classList.remove('btn-warning');
        saveNoticiaBtn.classList.add('btn-primary');
        cancelEditBtn.classList.add('d-none');
    }

    function formatForDatetimeLocal(isoDateString) {
        if (!isoDateString) return '';
        const date = new Date(isoDateString);
        if (isNaN(date.getTime())) {
            console.warn('formatForDatetimeLocal: Fecha inválida detectada:', isoDateString);
            return '';
        }
        return date.toISOString().slice(0, 16);
    }

    function formatDisplayDate(isoDateString) {
        if (!isoDateString) return 'N/A';
        const date = new Date(isoDateString);
        if (isNaN(date.getTime())) {
            console.warn('formatDisplayDate: Fecha inválida detectada:', isoDateString);
            return 'Fecha Inválida';
        }
        return date.toLocaleString('es-ES', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function createNoticiaRow(noticia) {
        const imageUrl = noticia.imagen ? `<img src="${noticia.imagen}" alt="Imagen de Noticia" style="max-width: 80px; max-height: 60px; object-fit: cover;">` : 'Sin imagen';
        const displayFecha = formatDisplayDate(noticia.fecha_publicacion);
        const truncatedDescription = noticia.descripcion && noticia.descripcion.length > 100 ?
                                     noticia.descripcion.substring(0, 97) + '...' :
                                     (noticia.descripcion || ''); // Asegura que sea un string o vacío

        return `
            <tr>
                <td>${noticia.id || 'N/A'}</td>
                <td>${noticia.titulo || 'Sin Título'}</td>
                <td>${truncatedDescription}</td>
                <td>${displayFecha}</td>
                <td>${noticia.url_origen ? `<a href="${noticia.url_origen}" target="_blank">Enlace</a>` : 'N/A'}</td>
                <td>${imageUrl}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-warning edit-btn me-2" data-id="${noticia.id}">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="${noticia.id}">
                        <i class="fas fa-trash-alt"></i> Eliminar
                    </button>
                </td>
            </tr>
        `;
    }

    // --- Renderizado de Noticias en la Tabla ---
    function renderNoticias(noticiasToRender) {
        console.log('renderNoticias: Preparando para renderizar', noticiasToRender.length, 'noticias.');
        noticiasTableBody.innerHTML = '';
        if (noticiasToRender.length > 0) {
            noticiasToRender.forEach(noticia => {
                noticiasTableBody.innerHTML += createNoticiaRow(noticia);
            });
            noNoticiasMessage.classList.add('d-none');
        } else {
            noNoticiasMessage.classList.remove('d-none');
            console.log('renderNoticias: No hay noticias para renderizar, mostrando mensaje.');
        }
        addEventListenersToButtons();
    }

    // --- Operaciones API ---

    async function fetchAllNoticias() {
        console.log(`WorkspaceAllNoticias: Realizando petición a: ${API_URL}`);
        try {
            const response = await fetch(API_URL);
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
            }
            const noticias = await response.json();
            console.log('fetchAllNoticias: Noticias recibidas de la API:', noticias); // IMPT: Verifica aquí los datos
            
            if (!Array.isArray(noticias)) {
                console.error("fetchAllNoticias: La respuesta de la API no es un array:", noticias);
                showMessage("Error: Los datos de noticias no tienen el formato esperado.", "danger");
                allNoticias = []; // Asegurarse de que sea un array vacío
            } else {
                allNoticias = noticias;
            }
            renderNoticias(allNoticias);
            console.log('fetchAllNoticias: allNoticias actualizado con', allNoticias.length, 'elementos.');
        } catch (error) {
            console.error('fetchAllNoticias: Error al cargar todas las noticias:', error);
            showMessage(`Error al cargar las noticias: ${error.message}`, 'danger');
            noticiasTableBody.innerHTML = '';
            noNoticiasMessage.classList.remove('d-none');
        }
    }

    async function saveNoticia(noticiaData, id = null) {
        const method = id ? 'PUT' : 'POST';
        const url = id ? `${API_URL}/${id}` : API_URL;

        console.log(`saveNoticia: ${method} request to ${url} with data:`, noticiaData);
        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(noticiaData)
            });

            const result = await response.json();

            if (!response.ok) {
                const errorMessage = result.message || 'Error desconocido al guardar la noticia.';
                throw new Error(`API error: ${errorMessage}`);
            }

            showMessage(`Noticia ${id ? 'actualizada' : 'creada'} con éxito.`, 'success');
            resetForm();
            fetchAllNoticias(); // Recargar todas las noticias para actualizar la lista y el estado de allNoticias
        } catch (error) {
            console.error('saveNoticia: Error al guardar la noticia:', error);
            showMessage(error.message, 'danger');
        }
    }

    async function deleteNoticia(id) {
        if (!confirm('¿Estás seguro de que quieres eliminar esta noticia?')) {
            return;
        }
        console.log(`deleteNoticia: DELETE request to ${API_URL}/${id}`);
        try {
            const response = await fetch(`${API_URL}/${id}`, {
                method: 'DELETE'
            });

            const result = await response.json();

            if (!response.ok) {
                const errorMessage = result.message || 'Error desconocido al eliminar la noticia.';
                throw new Error(`API error: ${errorMessage}`);
            }

            showMessage('Noticia eliminada con éxito.', 'success');
            fetchAllNoticias(); // Recargar todas las noticias
            resetForm();
        } catch (error) {
            console.error('deleteNoticia: Error al eliminar la noticia:', error);
            showMessage(error.message, 'danger');
        }
    }

    // --- Manejadores de Eventos ---

    noticiaForm.addEventListener('submit', (e) => {
        e.preventDefault();
        console.log('Formulario de noticia enviado.');

        const noticiaData = {
            titulo: tituloInput.value.trim(),
            descripcion: descripcionInput.value.trim(),
            fecha_publicacion: fechaPublicacionInput.value,
            url_origen: urlOrigenInput.value.trim(),
            imagen: imagenInput.value.trim()
        };

        if (noticiaData.titulo === '' || noticiaData.descripcion === '' || noticiaData.fecha_publicacion === '') {
            showMessage('Por favor, completa todos los campos requeridos (Título, Descripción, Fecha de Publicación).', 'warning');
            return;
        }

        saveNoticia(noticiaData, currentEditNoticiaId);
    });

    cancelEditBtn.addEventListener('click', resetForm);

    function addEventListenersToButtons() {
        document.querySelectorAll('.edit-btn').forEach(button => {
            // Eliminar listener previo para evitar duplicados si la tabla se renderiza múltiples veces
            button.removeEventListener('click', handleEditClick);
            button.addEventListener('click', handleEditClick);
        });

        document.querySelectorAll('.delete-btn').forEach(button => {
            // Eliminar listener previo para evitar duplicados
            button.removeEventListener('click', handleDeleteClick);
            button.addEventListener('click', handleDeleteClick);
        });
    }

    async function handleEditClick(event) {
        const id = event.target.closest('button').dataset.id;
        console.log('handleEditClick: Botón de editar clicado para ID:', id);
        try {
            const response = await fetch(`${API_URL}/${id}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const noticia = await response.json();
            console.log('handleEditClick: Datos de noticia para editar:', noticia);

            noticiaIdInput.value = noticia.id;
            tituloInput.value = noticia.titulo;
            descripcionInput.value = noticia.descripcion;
            fechaPublicacionInput.value = formatForDatetimeLocal(noticia.fecha_publicacion);
            urlOrigenInput.value = noticia.url_origen || '';
            imagenInput.value = noticia.imagen || '';

            currentEditNoticiaId = noticia.id;
            formTitleText.textContent = `Editar Noticia (ID: ${noticia.id})`;
            saveNoticiaBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Actualizar Noticia';
            saveNoticiaBtn.classList.remove('btn-primary');
            saveNoticiaBtn.classList.add('btn-warning');
            cancelEditBtn.classList.remove('d-none');

        } catch (error) {
            console.error('handleEditClick: Error al cargar la noticia para edición:', error);
            showMessage('Error al cargar la noticia para edición.', 'danger');
        }
    }

    function handleDeleteClick(event) {
        const id = event.target.closest('button').dataset.id;
        console.log('handleDeleteClick: Botón de eliminar clicado para ID:', id);
        deleteNoticia(id);
    }

    // --- Funcionalidad de Búsqueda en el Cliente ---

    const performSearch = () => {
        const searchTerm = searchInput.value.toLowerCase().trim();
        console.log('performSearch: Término de búsqueda actual:', `"${searchTerm}"`);
        console.log('performSearch: Total de noticias disponibles para buscar:', allNoticias.length);

        if (searchTerm === '') {
            console.log('performSearch: Término de búsqueda vacío. Renderizando todas las noticias.');
            renderNoticias(allNoticias);
            return;
        }

        const filteredNoticias = allNoticias.filter(noticia => {
            // Asegurarse de que 'noticia.titulo' exista y sea un string antes de usar toLowerCase()
            // Esto es CRÍTICO si tu API puede devolver null/undefined para 'titulo'
            const titulo = noticia.titulo ? String(noticia.titulo).toLowerCase() : '';
            const matches = titulo.includes(searchTerm);
            // console.log(`Noticia ID: ${noticia.id}, Título: "${noticia.titulo}", Coincide: ${matches}`); // Depuración por item
            return matches;
        });
        console.log('performSearch: Noticias filtradas (coincidencias por título):', filteredNoticias.length);
        renderNoticias(filteredNoticias);
    };

    // Escuchar el evento 'input' para una búsqueda en tiempo real
    if (searchInput) {
        searchInput.addEventListener('input', performSearch);
        console.log("EventListener 'input' añadido al searchInput.");
    }

    // --- Inicialización ---
    console.log('Iniciando fetchAllNoticias al cargar la página.');
    fetchAllNoticias(); // Cargar todas las noticias al iniciar la página
});