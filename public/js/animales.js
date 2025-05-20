// Archivo: /public/js/animales.js

// Este código se ejecutará cuando el script sea cargado dinámicamente por admin.js
console.log("animales.js cargado");

const API_ANIMALES_URL = '/api/v1/animales'; // !!! ASEGÚRATE DE QUE ESTA RUTA RELATIVA A TU WEBROOT (public) SEA CORRECTA !!!

// --- Funciones de Utilidad (Pueden estar en un archivo helpers.js compartido en public/js/) ---
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


// --- CRUD: Listar Animales (Función de inicialización llamada por admin.js) ---
async function loadAnimals() {
    console.log("Cargando animales...");
    // Referencias a elementos del DOM específicos de la sección animales
    // Estos elementos DEBEN existir en el HTML cargado (gracias a los includes en Twig)
    const animalsTableBody = document.getElementById('animalsTableBody');
    const animalListMessage = document.getElementById('animalListMessage');
    const editAnimalSection = document.getElementById('editAnimalSection');

    if (!animalsTableBody || !animalListMessage) {
         console.error('Elementos de tabla de animales o mensajes no encontrados al intentar cargar datos.');
         return;
    }

    hideMessage(animalListMessage);
    if(editAnimalSection) editAnimalSection.style.display = 'none'; // Ocultar form de edición al cargar la lista
    animalsTableBody.innerHTML = ''; // Limpiar la tabla antes de cargar

    try {
        const response = await fetch(API_ANIMALES_URL);
        const data = await response.json();

        if (!response.ok) {
            showMessage(animalListMessage, `Error al cargar animales: ${data.message || response.statusText}`, 'error');
            return;
        }

        if (data.length === 0) {
            animalsTableBody.innerHTML = '<tr><td colspan="7">No hay animales registrados.</td></tr>'; // 7 columnas
            return;
        }

        data.forEach(animal => {
            const row = animalsTableBody.insertRow();
            row.innerHTML = `
                <td>${animal.id}</td>
                <td><img src="${animal.imagen}" alt="${animal.nombre}" style="width: 50px; height: auto;"></td>
                <td>${animal.nombre}</td>
                <td>${animal.clase}</td>
                <td>${animal.continente}</td>
                <td>${animal.habitat}</td>
                <td>
                    <button class="button-warning edit-animal-button" data-animal-id="${animal.id}">Editar</button>
                    <button class="button-danger delete-animal-button" data-animal-id="${animal.id}">Eliminar</button>
                </td>
            `;
        });

    } catch (error) {
        console.error('Error fetching animals:', error);
        showMessage(animalListMessage, 'Ocurrió un error al conectar con la API para cargar animales.', 'error');
    }
}

// --- Exponer la función de inicialización globalmente ---
// El script admin.js la llamará después de cargar este archivo
window.loadAnimals = loadAnimals;


// --- Event Listeners (Se adjuntan cuando este script se ejecuta y el DOM está listo) ---
document.addEventListener('DOMContentLoaded', () => {
     console.log("DOMContentLoaded en animales.js");

    // Obtener referencias AHORA que el DOM está garantizado
    const animalsTableBody = document.getElementById('animalsTableBody');
    const addAnimalForm = document.getElementById('addAnimalForm');
    const addAnimalMessage = document.getElementById('addAnimalMessage');
    const editAnimalSection = document.getElementById('editAnimalSection');
    const editAnimalForm = document.getElementById('editAnimalForm');
    const editAnimalId = document.getElementById('editAnimalId');
    const editAnimalNombre = document.getElementById('editAnimalNombre');
    const editAnimalNombreCientifico = document.getElementById('editAnimalNombreCientifico');
    const editAnimalClase = document.getElementById('editAnimalClase');
    const editAnimalContinente = document.getElementById('editAnimalContinente');
    const editAnimalHabitat = document.getElementById('editAnimalHabitat');
    const editAnimalDieta = document.getElementById('editAnimalDieta');
    const editAnimalInformacion = document.getElementById('editAnimalInformacion');
    const editAnimalImagen = document.getElementById('editAnimalImagen');
    const editAnimalPeso = document.getElementById('editAnimalPeso');
    const editAnimalTamano = document.getElementById('editAnimalTamano');
    const editAnimalSabias = document.getElementById('editAnimalSabias');
    const editAnimalFechaNacimiento = document.getElementById('editAnimalFechaNacimiento');
    const editAnimalMessage = document.getElementById('editAnimalMessage');
    const cancelEditAnimalButton = document.getElementById('cancelEditAnimalButton');

    // --- CRUD: Añadir Animal ---
    if (addAnimalForm) { /* ... (código submit form añadir) ... */
        addAnimalForm.addEventListener('submit', async (e) => {
            e.preventDefault(); hideMessage(addAnimalMessage);
            const formData = new FormData(addAnimalForm);
            const animalData = {};
            formData.forEach((value, key) => {
                 if ((key === 'peso' || key === 'tamano') && value !== '') {
                     animalData[key] = parseFloat(value);
                 } else if (value !== '') { animalData[key] = value; }
            });
            try { /* ... fetch POST ... */
                const response = await fetch(API_ANIMALES_URL, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(animalData) });
                const result = await response.json();
                if (response.status === 201) {
                    showMessage(addAnimalMessage, 'Animal creado con éxito.', 'success'); clearForm(addAnimalForm); loadAnimals(); // Recargar la lista
                } else { showMessage(addAnimalMessage, `Error al crear animal: ${result.message || response.statusText}`, 'error'); }
            } catch (error) { console.error('Error adding animal:', error); showMessage(addAnimalMessage, 'Ocurrió un error al conectar con la API.', 'error'); }
        });
    }

    // --- CRUD: Editar Animal ---
    if (animalsTableBody) { /* ... (código delegación click editar) ... */
        animalsTableBody.addEventListener('click', async (e) => {
            if (e.target.classList.contains('edit-animal-button')) {
                const animalId = e.target.dataset.animalId; hideMessage(editAnimalMessage); hideMessage(document.getElementById('animalListMessage'));
                try { /* ... fetch GET ... */
                    const response = await fetch(`${API_ANIMALES_URL}/${animalId}`);
                    const animal = await response.json();
                    const animalListMessageElement = document.getElementById('animalListMessage');
                    if (!response.ok) {
                        if(animalListMessageElement) showMessage(animalListMessageElement, `Error al obtener datos del animal para editar: ${animal.message || response.statusText}`, 'error');
                         else console.error(`Error fetching animal ${animalId}:`, animal.message || response.statusText);
                         return;
                    }
                    if(editAnimalForm){ /* ... rellenar form y mostrar ... */
                         editAnimalId.value = animal.id; editAnimalNombre.value = animal.nombre; editAnimalNombreCientifico.value = animal.nombre_cientifico; editAnimalClase.value = animal.clase; editAnimalContinente.value = animal.continente; editAnimalHabitat.value = animal.habitat; editAnimalDieta.value = animal.dieta; editAnimalInformacion.value = animal.informacion; editAnimalImagen.value = animal.imagen; editAnimalPeso.value = animal.peso; editAnimalTamano.value = animal.tamano; editAnimalSabias.value = animal.sabias; editAnimalFechaNacimiento.value = animal.fecha_nacimiento;
                         if(editAnimalSection) editAnimalSection.style.display = 'block';
                    }
                } catch (error) { console.error('Error fetching animal for edit:', error); const animalListMessageElement = document.getElementById('animalListMessage'); if(animalListMessageElement) showMessage(animalListMessageElement, 'Ocurrió un error al obtener los datos del animal para editar.', 'error'); }
            }
        });
    }

    if (editAnimalForm) { /* ... (código submit form editar) ... */
         editAnimalForm.addEventListener('submit', async (e) => {
            e.preventDefault(); hideMessage(editAnimalMessage);
            const animalId = editAnimalId.value;
            const formData = new FormData(editAnimalForm);
             const updateData = {};
             formData.forEach((value, key) => {
                  if (key === 'id') return;
                  if ((key === 'peso' || key === 'tamano') && value !== '') { updateData[key] = parseFloat(value); }
                  else if (value !== '') { updateData[key] = value; }
             });
             if (!animalId) { showMessage(editAnimalMessage, 'Error: ID de animal para actualizar no encontrado.', 'error'); return; }
             if (Object.keys(updateData).length === 0) { showMessage(editAnimalMessage, 'No hay campos para actualizar.', 'warning'); if(editAnimalSection) editAnimalSection.style.display = 'none'; return; }
            try { /* ... fetch PUT ... */
                const response = await fetch(`${API_ANIMALES_URL}/${animalId}`, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(updateData) });
                 const result = await response.json();
                if (response.ok) {
                    showMessage(editAnimalMessage, 'Animal actualizado con éxito.', 'success');
                    if(editAnimalSection) editAnimalSection.style.display = 'none';
                    loadAnimals(); // Recargar la lista
                } else { showMessage(editAnimalMessage, `Error al actualizar animal: ${result.message || response.statusText}`, 'error'); }
            } catch (error) { console.error('Error updating animal:', error); showMessage(editAnimalMessage, 'Ocurrió un error al conectar con la API.', 'error'); }
         });
    }

    if (cancelEditAnimalButton && editAnimalSection) { /* ... (código cancelar editar) ... */
        cancelEditAnimalButton.addEventListener('click', () => { editAnimalSection.style.display = 'none'; hideMessage(editAnimalMessage); });
    }

    // --- CRUD: Eliminar Animal ---
     if (animalsTableBody) { /* ... (código delegación click eliminar) ... */
        animalsTableBody.addEventListener('click', async (e) => {
            if (e.target.classList.contains('delete-animal-button')) {
                const animalId = e.target.dataset.animalId;
                const animalListMessageElement = document.getElementById('animalListMessage');
                if (!confirm(`¿Estás seguro de que quieres eliminar al animal con ID ${animalId}?`)) return;
                if(animalListMessageElement) hideMessage(animalListMessageElement);
                try { /* ... fetch DELETE ... */
                    const response = await fetch(`${API_ANIMALES_URL}/${animalId}`, { method: 'DELETE' });
                    const result = await response.json();
                    if (response.ok) {
                        if(animalListMessageElement) showMessage(animalListMessageElement, `Animal ${animalId} eliminado con éxito.`, 'success');
                        const row = e.target.closest('tr'); if (row) row.remove();
                        if (animalsTableBody.children.length === 0) { animalsTableBody.innerHTML = '<tr><td colspan="7">No hay animales registrados.</td></tr>'; }
                    } else {
                         if(animalListMessageElement) showMessage(animalListMessageElement, `Error al eliminar animal ${animalId}: ${result.message || response.statusText}`, 'error');
                         else console.error(`Error deleting animal ${animalId}:`, result.message || response.statusText);
                    }
                } catch (error) { console.error('Error deleting animal:', error); if(animalListMessageElement) showMessage(animalListMessageElement, 'Ocurrió un error al conectar con la API.', 'error'); }
            }
        });
     }
});