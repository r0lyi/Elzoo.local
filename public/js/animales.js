// public/js/animales.js

document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = '/api/v1/animales';
    const API_FILTER_URL = '/api/v1/animales/filter';

    // Elementos del formulario principal (Crear/Editar)
    const animalForm = document.getElementById('animal-form');
    const animalIdField = document.getElementById('animal-id');
    const nombreField = document.getElementById('nombre');
    const nombreCientificoField = document.getElementById('nombre_cientifico');
    const claseField = document.getElementById('clase');
    const continenteField = document.getElementById('continente');
    const habitatField = document.getElementById('habitat');
    const dietaField = document.getElementById('dieta');
    const informacionField = document.getElementById('informacion');
    const imagenField = document.getElementById('imagen');
    const pesoField = document.getElementById('peso');
    const tamanoField = document.getElementById('tamano');
    const sabiasQueField = document.getElementById('sabias_que');
    const fechaRegistroField = document.getElementById('fecha_registro'); // Renombrado

    // Botones y títulos del formulario principal
    const saveAnimalBtn = document.getElementById('save-animal-btn');
    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    const formTitleText = document.getElementById('form-title-text');

    // Elementos de la tabla
    const animalsTableBody = document.querySelector('#animals-table tbody');
    const noAnimalsMessage = document.getElementById('no-animals-message');

    // Área de mensajes principal
    const messageArea = document.getElementById('message-area');

    // Elementos del Modal de Detalles del Animal
    const animalDetailsModal = new bootstrap.Modal(document.getElementById('animalDetailsModal'));
    const modalAnimalImage = document.getElementById('modal-animal-image');
    const modalAnimalName = document.getElementById('modal-animal-name');
    const modalAnimalScientificName = document.getElementById('modal-animal-scientific-name');
    const modalAnimalId = document.getElementById('modal-animal-id');
    const modalAnimalClase = document.getElementById('modal-animal-clase');
    const modalAnimalContinente = document.getElementById('modal-animal-continente');
    const modalAnimalHabitat = document.getElementById('modal-animal-habitat');
    const modalAnimalDieta = document.getElementById('modal-animal-dieta');
    const modalAnimalPeso = document.getElementById('modal-animal-peso');
    const modalAnimalTamano = document.getElementById('modal-animal-tamano');
    const modalAnimalFechaRegistro = document.getElementById('modal-animal-fecha-registro'); // Renombrado
    const modalAnimalInformacion = document.getElementById('modal-animal-informacion');
    const modalAnimalSabiasQue = document.getElementById('modal-animal-sabias-que');

    // Elementos del Formulario de Filtro
    const filterForm = document.getElementById('filter-form');
    const filterNombreField = document.getElementById('filter-nombre');
    const filterClaseField = document.getElementById('filter-clase');
    const filterContinenteField = document.getElementById('filter-continente');
    const filterDietaField = document.getElementById('filter-dieta');
    const filterPesoField = document.getElementById('filter-peso');
    const filterTamanoField = document.getElementById('filter-tamano');
    // filterFechaNacimientoField eliminado
    const applyFilterBtn = document.getElementById('apply-filter-btn');
    const clearFilterBtn = document.getElementById('clear-filter-btn');


    let editingAnimalId = null;

    // --- Funciones de Utilidad ---

    function showMessage(message, type = 'success', targetArea = messageArea) {
        targetArea.textContent = message;
        targetArea.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-info');
        
        if (type === 'success') {
            targetArea.classList.add('alert-success');
        } else if (type === 'error') {
            targetArea.classList.add('alert-danger');
        } else {
            targetArea.classList.add('alert-info');
        }
        
        setTimeout(() => {
            targetArea.classList.add('d-none');
        }, 5000);
    }

    function clearForm() {
        animalIdField.value = '';
        nombreField.value = '';
        nombreCientificoField.value = '';
        claseField.value = '';
        continenteField.value = '';
        habitatField.value = '';
        dietaField.value = '';
        informacionField.value = '';
        imagenField.value = '';
        pesoField.value = '';
        tamanoField.value = '';
        sabiasQueField.value = '';
        fechaRegistroField.value = ''; // Limpiar campo de fecha de registro
        fechaRegistroField.classList.remove('d-none'); // Asegurarse de que esté visible para un nuevo registro
        
        formTitleText.textContent = 'Registrar Nuevo Animal';
        saveAnimalBtn.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Animal';
        saveAnimalBtn.classList.remove('btn-warning');
        saveAnimalBtn.classList.add('btn-success');
        cancelEditBtn.classList.add('d-none');
        
        editingAnimalId = null;
    }

    function clearFilterForm() {
        filterNombreField.value = '';
        filterClaseField.value = '';
        filterContinenteField.value = '';
        filterDietaField.value = '';
        filterPesoField.value = '';
        filterTamanoField.value = '';
        fetchAnimals(); // Recargar todos los animales
    }

    // --- Peticiones a la API ---

    async function fetchAnimals(url = API_BASE_URL) {
        try {
            const response = await fetch(url);
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Error al obtener animales');
            }
            const animals = await response.json();
            renderAnimals(animals);
        } catch (error) {
            console.error('Error al obtener animales:', error);
            showMessage(`Error al cargar animales: ${error.message}`, 'error');
            animalsTableBody.innerHTML = '';
            noAnimalsMessage.classList.remove('d-none');
        }
    }

    async function fetchAnimalById(id) {
        try {
            const response = await fetch(`${API_BASE_URL}/${id}`);
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Error al obtener animal');
            }
            const animal = await response.json();
            
            // Llenar el formulario para edición
            animalIdField.value = animal.id;
            nombreField.value = animal.nombre;
            nombreCientificoField.value = animal.nombre_cientifico;
            claseField.value = animal.clase;
            continenteField.value = animal.continente;
            habitatField.value = animal.habitat;
            dietaField.value = animal.dieta;
            informacionField.value = animal.informacion;
            imagenField.value = animal.imagen;
            pesoField.value = animal.peso || '';
            tamanoField.value = animal.tamano || '';
            sabiasQueField.value = animal.sabias_que || '';
            
            // Ocultar o deshabilitar el campo fecha_registro en modo edición
            fechaRegistroField.value = animal.fecha_registro ? new Date(animal.fecha_registro).toLocaleString() : 'N/A';
            // Alternativa: Si quieres que no se vea nada en edición:
            // fechaRegistroField.value = '';
            // fechaRegistroField.closest('.col-md-6').classList.add('d-none'); // Oculta el div padre
            
            formTitleText.textContent = `Editar Animal (ID: ${animal.id})`;
            saveAnimalBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Actualizar Animal';
            saveAnimalBtn.classList.remove('btn-success');
            saveAnimalBtn.classList.add('btn-warning');
            cancelEditBtn.classList.remove('d-none');
            
            editingAnimalId = id;
            window.scrollTo({ top: 0, behavior: 'smooth' }); // Desplazar al formulario
        } catch (error) {
            console.error('Error al obtener animal por ID:', error);
            showMessage(`Error al cargar datos del animal: ${error.message}`, 'error');
            clearForm();
        }
    }

    // Muestra los detalles completos del animal en un modal
    async function showAnimalDetails(id) {
        try {
            const response = await fetch(`${API_BASE_URL}/${id}`);
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Error al obtener detalles del animal');
            }
            const animal = await response.json();

            modalAnimalImage.src = animal.imagen || '/assets/img/default_animal.jpg'; // **ACTUALIZA ESTA RUTA DE IMAGEN POR DEFECTO**
            modalAnimalName.textContent = animal.nombre;
            modalAnimalScientificName.textContent = `(${animal.nombre_cientifico})`;
            modalAnimalId.textContent = animal.id;
            modalAnimalClase.textContent = animal.clase;
            modalAnimalContinente.textContent = animal.continente;
            modalAnimalHabitat.textContent = animal.habitat;
            modalAnimalDieta.textContent = animal.dieta;
            modalAnimalPeso.textContent = animal.peso !== null ? `${animal.peso}` : 'N/A';
            modalAnimalTamano.textContent = animal.tamano !== null ? `${animal.tamano}` : 'N/A';
            modalAnimalFechaRegistro.textContent = animal.fecha_registro ? new Date(animal.fecha_registro).toLocaleString() : 'N/A'; // Renombrado y formateado
            modalAnimalInformacion.textContent = animal.informacion;
            modalAnimalSabiasQue.textContent = animal.sabias_que || 'No hay curiosidades registradas.';

            animalDetailsModal.show();
        } catch (error) {
            console.error('Error al mostrar detalles del animal:', error);
            showMessage(`Error al cargar detalles: ${error.message}`, 'error');
        }
    }

    async function saveAnimal(event) {
        event.preventDefault();

        const animalData = {
            nombre: nombreField.value.trim(),
            nombre_cientifico: nombreCientificoField.value.trim(),
            clase: claseField.value.trim(),
            continente: continenteField.value.trim(),
            habitat: habitatField.value.trim(),
            dieta: dietaField.value.trim(),
            informacion: informacionField.value.trim(),
            imagen: imagenField.value.trim(),
            peso: pesoField.value !== '' ? parseFloat(pesoField.value) : null,
            tamano: tamanoField.value !== '' ? parseFloat(tamanoField.value) : null,
            sabias_que: sabiasQueField.value.trim() !== '' ? sabiasQueField.value.trim() : null,
            // fecha_registro NO se envía, el backend/DB la maneja
        };

        let method = 'POST';
        let url = API_BASE_URL;

        const requiredFields = ['nombre', 'nombre_cientifico', 'clase', 'continente', 'habitat', 'dieta', 'informacion', 'imagen'];
        for (const field of requiredFields) {
            if (!animalData[field]) {
                showMessage(`El campo '${field}' es requerido.`, 'error');
                return;
            }
        }
        
        if (editingAnimalId) {
            method = 'PUT';
            url = `${API_BASE_URL}/${editingAnimalId}`;
            // En modo edición, no enviamos fecha_registro aunque existiera en el objeto animalData
            // ya que no se permite su modificación.
            delete animalData.fecha_registro; 
        }

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(animalData),
            });

            const responseData = await response.json();

            if (!response.ok) {
                throw new Error(responseData.message || 'Error al guardar animal');
            }

            showMessage(responseData.message || `Animal ${editingAnimalId ? 'actualizado' : 'creado'} con éxito.`, 'success');
            clearForm();
            fetchAnimals();
        } catch (error) {
            console.error('Error al guardar animal:', error);
            showMessage(`Error al guardar animal: ${error.message}`, 'error');
        }
    }

    async function deleteAnimal(id) {
        if (!confirm('¿Estás seguro de que quieres eliminar este animal?')) {
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}/${id}`, {
                method: 'DELETE',
            });

            const responseData = await response.json();

            if (!response.ok) {
                throw new Error(responseData.message || 'Error al eliminar animal');
            }

            showMessage(responseData.message || 'Animal eliminado con éxito.', 'success');
            fetchAnimals();
        } catch (error) {
            console.error('Error al eliminar animal:', error);
            showMessage(`Error al eliminar animal: ${error.message}`, 'error');
        }
    }

    // --- Renderizado de la Tabla ---

    function renderAnimals(animals) {
        animalsTableBody.innerHTML = '';
        if (animals.length === 0) {
            noAnimalsMessage.classList.remove('d-none');
            return;
        }
        noAnimalsMessage.classList.add('d-none');

        animals.forEach(animal => {
            const row = animalsTableBody.insertRow();
            row.insertCell().textContent = animal.id;
            
            // Columna de Imagen
            const imageCell = row.insertCell();
            const img = document.createElement('img');
            img.src = animal.imagen || '/assets/img/default_animal_thumbnail.jpg'; // **ACTUALIZA ESTA RUTA DE IMAGEN POR DEFECTO PARA LA TABLA**
            img.alt = animal.nombre;
            img.classList.add('img-thumbnail');
            img.style.width = '50px';
            img.style.height = '50px';
            img.style.objectFit = 'cover';
            imageCell.appendChild(img);

            row.insertCell().textContent = animal.nombre;
            row.insertCell().textContent = animal.nombre_cientifico;
            row.insertCell().textContent = animal.clase;
            row.insertCell().textContent = animal.continente;

            const actionsCell = row.insertCell();
            actionsCell.classList.add('text-center');

            const viewButton = document.createElement('button');
            viewButton.innerHTML = '<i class="fas fa-eye"></i>';
            viewButton.classList.add('btn', 'btn-info', 'btn-sm', 'me-2');
            viewButton.setAttribute('title', 'Ver Detalles');
            viewButton.addEventListener('click', () => showAnimalDetails(animal.id));
            actionsCell.appendChild(viewButton);

            const editButton = document.createElement('button');
            editButton.innerHTML = '<i class="fas fa-edit"></i>';
            editButton.classList.add('btn', 'btn-warning', 'btn-sm', 'me-2');
            editButton.setAttribute('title', 'Editar Animal');
            editButton.addEventListener('click', () => fetchAnimalById(animal.id));
            actionsCell.appendChild(editButton);

            const deleteButton = document.createElement('button');
            deleteButton.innerHTML = '<i class="fas fa-trash-alt"></i>';
            deleteButton.classList.add('btn', 'btn-danger', 'btn-sm');
            deleteButton.setAttribute('title', 'Eliminar Animal');
            deleteButton.addEventListener('click', () => deleteAnimal(animal.id));
            actionsCell.appendChild(deleteButton);
        });
    }

    // --- Lógica de Filtrado ---

    function applyFilters(event) {
        event.preventDefault();
        const filters = {};

        if (filterNombreField.value.trim() !== '') {
            filters.nombre = filterNombreField.value.trim();
        }
        if (filterClaseField.value.trim() !== '') {
            filters.clase = filterClaseField.value.trim();
        }
        if (filterContinenteField.value.trim() !== '') {
            filters.continente = filterContinenteField.value.trim();
        }
        if (filterDietaField.value.trim() !== '') {
            filters.dieta = filterDietaField.value.trim();
        }
        if (filterPesoField.value !== '') {
            filters.peso = parseFloat(filterPesoField.value);
        }
        if (filterTamanoField.value !== '') {
            filters.tamano = parseFloat(filterTamanoField.value);
        }

        const queryString = new URLSearchParams(filters).toString();
        const filterUrl = `${API_FILTER_URL}?${queryString}`;
        
        fetchAnimals(filterUrl);
    }

    // --- Event Listeners ---

    animalForm.addEventListener('submit', saveAnimal);
    cancelEditBtn.addEventListener('click', clearForm);

    filterForm.addEventListener('submit', applyFilters);
    clearFilterBtn.addEventListener('click', clearFilterForm);

    // Cargar animales al cargar la página (inicialmente sin filtros)
    fetchAnimals();
});