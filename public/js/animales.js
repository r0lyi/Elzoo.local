// public/js/animales.js

document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const animalsTableBody = document.getElementById('animalsTableBody');
    const animalAlertMessage = document.getElementById('animal-alert-message');
    const refreshAnimalsBtn = document.getElementById('refreshAnimalsBtn');
    const addAnimalForm = document.getElementById('addAnimalForm');
    const editAnimalForm = document.getElementById('editAnimalForm');
    const confirmDeleteAnimalBtn = document.getElementById('confirmDeleteAnimalBtn');

    // Modals de Bootstrap (instancias para controlar su visibilidad)
    const addAnimalModal = new bootstrap.Modal(document.getElementById('addAnimalModal'));
    const editAnimalModal = new bootstrap.Modal(document.getElementById('editAnimalModal'));
    const deleteAnimalModal = new bootstrap.Modal(document.getElementById('deleteAnimalModal'));

    // Función para mostrar mensajes de alerta
    function showAlert(message, type = 'success') {
        animalAlertMessage.textContent = message;
        animalAlertMessage.className = `alert alert-${type} d-block`;
        setTimeout(() => {
            animalAlertMessage.classList.add('d-none');
        }, 5000);
    }

    // Función para renderizar la tabla de animales
    function renderAnimalsTable(animals) {
        animalsTableBody.innerHTML = ''; // Limpiar la tabla

        if (animals.length === 0) {
            animalsTableBody.innerHTML = '<tr><td colspan="9" class="text-center p-4">No hay animales registrados.</td></tr>';
            return;
        }

        animals.forEach(animal => {
            const row = `
                <tr>
                    <td>${animal.id}</td>
                    <td><img src="${animal.imagen || '/img/default-animal.png'}" alt="${animal.nombre}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;"></td>
                    <td>${animal.nombre}</td>
                    <td>${animal.nombre_cientifico}</td>
                    <td>${animal.clase}</td>
                    <td>${animal.continente}</td>
                    <td>${animal.habitat}</td>
                    <td>${animal.dieta}</td>
                    <td>
                        <button class="btn btn-sm btn-info me-2 edit-animal-btn" data-id="${animal.id}">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-sm btn-danger delete-animal-btn" data-id="${animal.id}" data-name="${animal.nombre}">
                            <i class="fas fa-trash-alt"></i> Eliminar
                        </button>
                    </td>
                </tr>
            `;
            animalsTableBody.insertAdjacentHTML('beforeend', row);
        });

        // Adjuntar eventos a los nuevos botones
        attachTableButtonListeners();
    }

    // Función para cargar animales desde la API
    async function loadAnimals() {
        animalsTableBody.innerHTML = '<tr><td colspan="9" class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i> Cargando animales...</td></tr>';
        try {
            const response = await fetch('/api/v1/animales'); // Tu endpoint de la API
            const data = await response.json();
            if (response.ok) {
                renderAnimalsTable(data);
            } else {
                showAlert(`Error al cargar animales: ${data.message || response.statusText}`, 'danger');
                animalsTableBody.innerHTML = '<tr><td colspan="9" class="text-center text-danger p-4">Error al cargar los animales.</td></tr>';
            }
        } catch (error) {
            console.error('Error fetching animals:', error);
            showAlert('Error de conexión al cargar animales.', 'danger');
            animalsTableBody.innerHTML = '<tr><td colspan="9" class="text-center text-danger p-4">Error de conexión.</td></tr>';
        }
    }

    // Función para añadir un nuevo animal
    addAnimalForm.addEventListener('submit', async function(event) {
        event.preventDefault();

        const animalData = {
            nombre: document.getElementById('addAnimalNombre').value,
            nombre_cientifico: document.getElementById('addAnimalNombreCientifico').value,
            clase: document.getElementById('addAnimalClase').value,
            continente: document.getElementById('addAnimalContinente').value,
            habitat: document.getElementById('addAnimalHabitat').value,
            dieta: document.getElementById('addAnimalDieta').value,
            informacion: document.getElementById('addAnimalInformacion').value,
            imagen: document.getElementById('addAnimalImagen').value,
            peso: document.getElementById('addAnimalPeso').value,
            tamano: document.getElementById('addAnimalTamano').value,
            sabias: document.getElementById('addAnimalSabias').value,
            fecha_nacimiento: document.getElementById('addAnimalFechaNacimiento').value
        };

        // Eliminar campos vacíos u opcionales para no enviarlos si no tienen valor
        for (const key in animalData) {
            if (animalData[key] === null || animalData[key] === '') {
                delete animalData[key];
            }
        }

        try {
            const response = await fetch('/api/v1/animales', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(animalData)
            });

            const data = await response.json();

            if (response.ok) {
                showAlert('Animal añadido con éxito.', 'success');
                addAnimalModal.hide();
                addAnimalForm.reset();
                loadAnimals();
            } else {
                showAlert(`Error al añadir animal: ${data.message || 'Error desconocido.'}`, 'danger');
            }
        } catch (error) {
            console.error('Error adding animal:', error);
            showAlert('Error de conexión al añadir animal.', 'danger');
        }
    });

    // Función para editar un animal existente
    editAnimalForm.addEventListener('submit', async function(event) {
        event.preventDefault();

        const id = document.getElementById('editAnimalId').value;
        const animalData = {
            nombre: document.getElementById('editAnimalNombre').value,
            nombre_cientifico: document.getElementById('editAnimalNombreCientifico').value,
            clase: document.getElementById('editAnimalClase').value,
            continente: document.getElementById('editAnimalContinente').value,
            habitat: document.getElementById('editAnimalHabitat').value,
            dieta: document.getElementById('editAnimalDieta').value,
            informacion: document.getElementById('editAnimalInformacion').value,
            imagen: document.getElementById('editAnimalImagen').value,
            peso: document.getElementById('editAnimalPeso').value,
            tamano: document.getElementById('editAnimalTamano').value,
            sabias: document.getElementById('editAnimalSabias').value,
            fecha_nacimiento: document.getElementById('editAnimalFechaNacimiento').value
        };

        // Eliminar campos vacíos u opcionales para no enviarlos si no tienen valor
        for (const key in animalData) {
            if (animalData[key] === null || animalData[key] === '') {
                delete animalData[key];
            }
        }

        try {
            const response = await fetch(`/api/v1/animales/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(animalData)
            });

            const data = await response.json();

            if (response.ok) {
                showAlert('Animal actualizado con éxito.', 'success');
                editAnimalModal.hide();
                loadAnimals();
            } else {
                showAlert(`Error al actualizar animal: ${data.message || 'Error desconocido.'}`, 'danger');
            }
        } catch (error) {
            console.error('Error updating animal:', error);
            showAlert('Error de conexión al actualizar animal.', 'danger');
        }
    });

    // Función para eliminar un animal
    confirmDeleteAnimalBtn.addEventListener('click', async function() {
        const id = document.getElementById('deleteAnimalIdConfirm').value;

        try {
            const response = await fetch(`/api/v1/animales/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok) {
                showAlert(data.message || 'Animal eliminado con éxito.', 'success');
                deleteAnimalModal.hide();
                loadAnimals();
            } else {
                showAlert(`Error al eliminar animal: ${data.message || 'Error desconocido.'}`, 'danger');
            }
        } catch (error) {
            console.error('Error deleting animal:', error);
            showAlert('Error de conexión al eliminar animal.', 'danger');
        }
    });

    // Delegación de eventos para botones de la tabla (Editar y Eliminar)
    function attachTableButtonListeners() {
        animalsTableBody.querySelectorAll('.edit-animal-btn').forEach(button => {
            button.addEventListener('click', async function() {
                const id = this.dataset.id;
                try {
                    const response = await fetch(`/api/v1/animales/${id}`);
                    const animal = await response.json();
                    if (response.ok) {
                        // Rellenar el modal de edición con los datos del animal
                        document.getElementById('editAnimalId').value = animal.id;
                        document.getElementById('editAnimalNombre').value = animal.nombre || '';
                        document.getElementById('editAnimalNombreCientifico').value = animal.nombre_cientifico || '';
                        document.getElementById('editAnimalClase').value = animal.clase || '';
                        document.getElementById('editAnimalContinente').value = animal.continente || '';
                        document.getElementById('editAnimalHabitat').value = animal.habitat || '';
                        document.getElementById('editAnimalDieta').value = animal.dieta || '';
                        document.getElementById('editAnimalInformacion').value = animal.informacion || '';
                        document.getElementById('editAnimalImagen').value = animal.imagen || '';
                        document.getElementById('editAnimalPeso').value = animal.peso || '';
                        document.getElementById('editAnimalTamano').value = animal.tamano || '';
                        document.getElementById('editAnimalSabias').value = animal.sabias || '';
                        document.getElementById('editAnimalFechaNacimiento').value = animal.fecha_nacimiento || '';

                        editAnimalModal.show();
                    } else {
                        showAlert(`No se pudo cargar el animal para edición: ${animal.message || 'Error desconocido.'}`, 'danger');
                    }
                } catch (error) {
                    console.error('Error fetching animal for edit:', error);
                    showAlert('Error de conexión al cargar datos del animal para edición.', 'danger');
                }
            });
        });

        animalsTableBody.querySelectorAll('.delete-animal-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;

                // Rellenar el modal de confirmación
                document.getElementById('deleteAnimalIdConfirm').value = id;
                document.getElementById('deleteAnimalNameConfirm').textContent = name;

                deleteAnimalModal.show();
            });
        });
    }

    // Evento para el botón de refrescar
    refreshAnimalsBtn.addEventListener('click', loadAnimals);

    // Búsqueda en la tabla (cliente-side)
    const animalSearchInput = document.getElementById('animalSearchInput');
    const animalSearchBtn = document.getElementById('animalSearchBtn');

    animalSearchBtn.addEventListener('click', function() {
        const searchTerm = animalSearchInput.value.toLowerCase();
        const rows = animalsTableBody.querySelectorAll('tr');
        rows.forEach(row => {
            // Unir el texto de todas las celdas relevantes para la búsqueda
            const rowText = Array.from(row.children).slice(2, 8).map(td => td.textContent.toLowerCase()).join(' '); // Nombre, Científico, Clase, Continente, Hábitat, Dieta
            if (rowText.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    animalSearchInput.addEventListener('keyup', function() {
        if (this.value.length > 2 || this.value.length === 0) {
            animalSearchBtn.click();
        }
    });

    // Cargar animales cuando el script se ejecute
    loadAnimals();
});