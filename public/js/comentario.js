// public/js/comentario.js
// Gestiona las operaciones y la vista de la sección de Comentarios, usualmente mostrada dentro de la sección de Foros.
// Depende de foro.js para la acción de "volver a la lista de foros".

const ComentariosManager = {
    contentArea: null, // Referencia al div principal donde se renderiza el contenido
    apiBaseUrl: '', // URL base de la API
    currentForoId: null, // ID del foro cuyos comentarios se están mostrando
    backToForosCallback: null, // Callback para volver a la lista de foros

    /**
     * Carga y muestra la lista de comentarios para un foro específico.
     * Esta es la función de "inicio" para la vista de comentarios, llamada desde ForosManager.
     * @param {string} foroId - El ID del foro cuyos comentarios cargar.
     * @param {HTMLElement} contentDiv - El div principal donde Comentarios renderizará su contenido.
     * @param {string} baseUrl - La URL base de la API.
     * @param {function} backCallback - La función a llamar para volver a la lista de foros.
     */
    loadComentariosForoList: async function(foroId, contentDiv, baseUrl, backCallback) {
        this.currentForoId = foroId; // Guardar el ID del foro actual
        this.contentArea = contentDiv; // Guardar referencia al área de contenido
        this.apiBaseUrl = baseUrl; // Guardar URL base de la API
        this.backToForosCallback = backCallback; // Guardar el callback para volver

        // Limpiar y mostrar título y mensaje de carga
        this.contentArea.innerHTML = `<h2>Gestionar Comentarios del Foro ${foroId}</h2><div class="loading">Cargando comentarios...</div>`;
         const listContainer = document.createElement('div'); // Usar un contenedor para la lista y formulario
        listContainer.className = 'resource-section';
        this.contentArea.appendChild(listContainer);


        try {
            // Realizar petición GET a la API de comentarios para un foro específico
            // URL: /api/v1/foros/{foro_id}/comentarios
            const response = await fetch(`${this.apiBaseUrl}/foros/${foroId}/comentarios`);

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(`Error HTTP ${response.status}: ${errorData.message || response.statusText}`);
            }

            const comentarios = await response.json();

             this.contentArea.querySelector('.loading').remove(); // Eliminar mensaje de carga
             listContainer.innerHTML = ''; // Limpiar contenedor

            // Botón para volver a la lista de foros
            let backButtonHtml = `<button class="btn btn-secondary mb-3" data-action="back-to-foros-list">← Volver a la Lista de Foros</button>`; // Flecha para mejor UX
            listContainer.innerHTML += backButtonHtml;


            this.renderComentariosList(comentarios, listContainer); // Renderizar la lista de comentarios
            this.addComentarioListEventListeners(listContainer); // Añadir listeners específicos de comentario

            console.log(`Comentarios cargados para Foro ${foroId}:`, comentarios);


        } catch (error) {
            console.error(`Error al cargar la lista de comentarios para el foro ${foroId}:`, error);
            this.contentArea.innerHTML = `<h2>Gestionar Comentarios del Foro ${foroId}</h2><div class="error">Error al cargar comentarios: ${error.message}</div>`;
             // Botón para volver incluso en caso de error
            const backButton = document.createElement('button');
            backButton.className = 'btn btn-secondary mt-3';
            backButton.textContent = '← Volver a la Lista de Foros';
            // Usar el callback almacenado para volver
            backButton.addEventListener('click', () => {
                if (this.backToForosCallback) this.backToForosCallback();
            });
            this.contentArea.appendChild(backButton);
        }
    },

    /**
     * Renderiza la tabla de comentarios en el contenedor dado.
     * @param {Array<object>} comentarios - Array de objetos comentario.
     * @param {HTMLElement} container - El elemento HTML donde se renderizará la lista.
     */
    renderComentariosList: function(comentarios, container) {
        // Botón Añadir Nuevo Comentario para este foro
         let html = `<button class="btn btn-primary mb-3 ml-2" data-action="add-new-comentario" data-foro-id="${this.currentForoId}">Añadir Nuevo Comentario</button>`;

        if (comentarios.length === 0) {
            html += '<p>No hay comentarios para este foro.</p>';
        } else {
             html += '<h3>Comentarios</h3>' // Subtítulo para la lista de comentarios
            html += '<div class="data-list">'; // Contenedor para la tabla
            html += '<table><thead><tr>';
            // Ajusta los encabezados según los campos de tu tabla comentarios (y si incluyes info del autor)
            html += '<th>ID</th><th>Contenido</th><th>Usuario ID</th><th>Autor Nombre</th><th>Fecha</th><th>Acciones</th>';
            html += '</tr></thead><tbody>';

            comentarios.forEach(comentario => {
                 // Asegúrate de que comentario.usuario_id, comentario.autor_nombre y comentario.fecha existan en los datos de la API
                html += `<tr>
                    <td>${comentario.id}</td>
                    <td>${comentario.contenido}</td>
                    <td>${comentario.usuario_id || 'N/A'}</td>
                    <td>${comentario.autor_nombre || 'N/A'}</td> {# Mostrar info del autor si la API la proporciona #}
                    <td>${comentario.fecha || 'N/A'}</td>
                    <td class="actions">
                        <button class="btn btn-sm btn-info" data-action="view-edit-comentario" data-id="${comentario.id}">Ver/Editar</button>
                        <button class="btn btn-sm btn-danger" data-action="delete-comentario" data-id="${comentario.id}">Eliminar</button>
                    </td>
                </tr>`;
            });

            html += '</tbody></table>';
            html += '</div>'; // Cerrar contenedor data-list
        }
        container.innerHTML += html; // Añadir el HTML al contenedor existente (después del botón volver)
    },

     /**
     * Añade event listeners a los botones dentro de la lista de comentarios.
     * @param {HTMLElement} container - El contenedor que contiene la lista.
     */
     addComentarioListEventListeners: function(container) {
         // Listener para el botón Volver a Foros (ya añadido en loadComentariosForoList)
         container.querySelector('button[data-action="back-to-foros-list"]').addEventListener('click', () => {
             if (this.backToForosCallback) this.backToForosCallback(); // Llama al callback almacenado para volver a la lista de foros
         });

         // Listener para el botón "Añadir Nuevo Comentario"
          const addCommentButton = container.querySelector('button[data-action="add-new-comentario"]');
          if(addCommentButton) {
              addCommentButton.addEventListener('click', (event) => {
                   // Llamar a la función para mostrar el formulario, pasando solo el ID del comentario (null para nuevo)
                   // y el ID del foro (que ya está en this.currentForoId)
                   this.showComentarioForm(null); // showComentarioForm usará this.currentForoId automáticamente
              });
          }

         // Listeners para los botones de acción de comentarios (Ver/Editar, Eliminar)
        container.querySelectorAll('.data-list button').forEach(button => {
            button.addEventListener('click', (event) => {
                const action = event.target.dataset.action;
                const id = event.target.dataset.id;
                 // El foroId actual ya está almacenado en this.currentForoId

                if (action === 'view-edit-comentario') {
                    this.showComentarioForm(id); // Pasar solo el ID del comentario
                } else if (action === 'delete-comentario') {
                     if (confirm(`¿Estás seguro de que quieres eliminar el comentario con ID ${id}?`)) {
                        this.deleteComentarioItem(id);
                     }
                }
            });
        });
    },

    /**
     * Muestra el formulario para crear o editar un comentario.
     * @param {string|null} comentarioId - El ID del comentario si es para editar, null si es para crear.
     * Nota: Asume que this.currentForoId ya está establecido si es para crear.
     */
    showComentarioForm: async function(comentarioId = null) {
        // TODO: Implementar carga de datos (si es edición), renderizado y listeners del formulario de comentario
        console.log(`Mostrar formulario de comentario para ID: ${comentarioId || 'Nuevo'} (Foro ID: ${this.currentForoId})`);

         // Asegurarse de que currentForoId esté establecido si se intenta crear un comentario
         const isEditing = comentarioId !== null;
         const foroIdToUse = this.currentForoId; // El foro ID actual es necesario para el contexto

         if (!isEditing && !foroIdToUse) {
              console.error("Error: No se pudo determinar el Foro ID para crear el comentario.");
               this.contentArea.innerHTML = `<h2>Gestionar Foros</h2><div class="alert alert-danger" role="alert">Error al crear comentario: No se pudo determinar el Foro asociado.</div>`;
               // Botón para volver a la lista de foros principal si no hay foroId
               const backButton = document.createElement('button');
               backButton.className = 'btn btn-secondary mt-3';
               backButton.textContent = '← Volver a la Lista de Foros';
               backButton.addEventListener('click', () => { if(this.backToForosCallback) this.backToForosCallback(); });
               this.contentArea.appendChild(backButton);
              return;
         }

         const formTitle = isEditing ? `Editar Comentario (ID: ${comentarioId})` : `Añadir Nuevo Comentario al Foro ${foroIdToUse}`;
         const formContainer = document.createElement('div');
         formContainer.className = 'resource-section dynamic-form';
         // Mantener el título principal de Foros, o cambiar a "Gestionar Comentarios" si se prefiere
         this.contentArea.innerHTML = `<h2>Gestionar Comentarios del Foro ${foroIdToUse}</h2>`; // Actualizar título principal
         this.contentArea.appendChild(formContainer);

         let comentarioData = {}; // Datos para pre-llenar el formulario

         if (isEditing) {
             formContainer.innerHTML = '<h3>Cargando Datos de Comentario...</h3><div class="loading"></div>';
             try {
                  // Cargar datos del comentario específico desde la API de comentarios por ID
                  // URL: /api/v1/comentarios/{id}
                  const response = await fetch(`${this.apiBaseUrl}/comentarios/${comentarioId}`);
                  if (!response.ok) {
                      const errorData = await response.json();
                      throw new Error(`Error HTTP ${response.status}: ${errorData.message || response.statusText}`);
                  }
                  comentarioData = await response.json();
                  formContainer.innerHTML = ''; // Clear loading
             } catch(error) {
                 console.error(`Error loading comentario (ID: ${comentarioId}):`, error);
                 formContainer.innerHTML = `<h3>Error al cargar datos de Comentario</h3><div class="error">${error.message}</div>`;
                 const backButton = document.createElement('button');
                 backButton.className = 'btn btn-secondary mt-3';
                 backButton.textContent = '← Volver a Comentarios';
                 backButton.addEventListener('click', () => this.loadComentariosForoList(this.currentForoId, this.contentArea, this.apiBaseUrl, this.backToForosCallback)); // Volver a la lista de comentarios del foro actual
                 formContainer.appendChild(backButton);
                 return;
             }
         } else {
              // Si es para crear
              comentarioData = {
                  contenido: '',
                  usuario_id: '', // Asumiendo que se necesita el usuario ID del autor del comentario
                  foro_id: foroIdToUse // Incluir el ID del foro actual
              };
              formContainer.innerHTML = ''; // Clear if not editing
         }


         const submitButtonText = isEditing ? 'Actualizar Comentario' : 'Crear Comentario';
         const formAction = isEditing ? 'update' : 'create';

         let formHtml = `<h3>${formTitle}</h3>
             <form id="comentario-dynamic-form" data-action="${formAction}" data-id="${isEditing ? comentarioId : ''}">
                 <div class="form-group">
                     <label for="comentario-contenido">Contenido:</label>
                     <textarea id="comentario-contenido" name="contenido" required>${comentarioData.contenido}</textarea>
                 </div>
                 <div class="form-group">
                      {# Asumiendo que tu tabla comentarios tiene usuario_id y es necesario para crear/editar #}
                     <label for="comentario-usuario_id">Usuario Autor (ID):</label>
                     <input type="number" id="comentario-usuario_id" name="usuario_id" value="${comentarioData.usuario_id}" required min="1">
                      {# TODO: Sería ideal cargar una lista desplegable de usuarios y seleccionar por nombre #}
                 </div>
                 {# Campo oculto para el foro_id si es una creación. Si es edición, no se necesita enviar en el body usualmente. #}
                 ${isEditing ? '' : `<input type="hidden" name="foro_id" value="${comentarioData.foro_id}">`}


                 <button type="submit" class="btn btn-success">${submitButtonText}</button>
                 <button type="button" class="btn btn-secondary" data-action="cancel-comentario-form">Cancelar</button>
             </form>`;

         formContainer.innerHTML = formHtml;

         const form = formContainer.querySelector('#comentario-dynamic-form');
         // Usar bind(this) para mantener el contexto del objeto ComentariosManager
         form.addEventListener('submit', this.handleComentarioFormSubmit.bind(this));

         // Listener para el botón Cancelar
         formContainer.querySelector('button[data-action="cancel-comentario-form"]').addEventListener('click', () => {
             // Volver a la lista de comentarios del foro actual
             this.loadComentariosForoList(this.currentForoId, this.contentArea, this.apiBaseUrl, this.backToForosCallback);
         });
    },

    /**
     * Maneja el envío del formulario de comentarios (Crear o Actualizar).
     * @param {Event} event - El evento de envío del formulario.
     */
    handleComentarioFormSubmit: async function(event) {
        event.preventDefault(); // Prevenir el envío normal del formulario
        console.log("Formulario de comentario enviado!");

        const form = event.target;
        const action = form.dataset.action; // 'create' o 'update'
        const comentarioId = form.dataset.id; // ID si es 'update'

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

         // Convertir campos numéricos a número si existen
         if (data.usuario_id) data.usuario_id = parseInt(data.usuario_id, 10);
         if (data.foro_id) data.foro_id = parseInt(data.foro_id, 10); // Debería estar en el hidden input si es creación


        let url;
        let method;
         const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.textContent = 'Guardando...';

        if (action === 'create') {
            // Para crear, usamos la URL anidada del foro
            const foroId = data.foro_id; // Obtener el foro_id del hidden input
            if (!foroId) {
                console.error("Error: Foro ID no encontrado para crear comentario.");
                this.contentArea.innerHTML = `<h2>Gestionar Foros</h2><div class="alert alert-danger" role="alert">Error al crear comentario: Foro ID no encontrado.</div>`;
                 const backButton = document.createElement('button');
                 backButton.className = 'btn btn-secondary mt-3';
                 backButton.textContent = '← Volver a la Lista de Foros';
                 backButton.addEventListener('click', () => { if(this.backToForosCallback) this.backToForosCallback(); }); // Volver a la lista de foros
                 this.contentArea.appendChild(backButton);
                return;
            }
            // URL para crear comentario: /api/v1/foros/{foro_id}/comentarios
            url = `${this.apiBaseUrl}/foros/${foroId}/comentarios`;
            method = 'POST';
             // Eliminar foro_id del cuerpo si la API solo lo espera en la URL
             // delete data.foro_id; // Descomentar si tu API POST /foros/{id}/comentarios ignora foro_id en el body
        } else if (action === 'update') {
            // Para actualizar, usamos la URL por ID del comentario
            // URL para actualizar comentario: /api/v1/comentarios/{id}
            url = `${this.apiBaseUrl}/comentarios/${comentarioId}`;
            method = 'PUT';
             delete data.id; // No enviar ID en el body
             // Eliminar foro_id y usuario_id del cuerpo al actualizar si no se permite cambiarlos
             // delete data.foro_id;
             // delete data.usuario_id;
        } else {
             console.error("Acción de formulario de comentario desconocida:", action);
              this.contentArea.innerHTML = `<h2>Gestionar Foros</h2><div class="alert alert-danger" role="alert">Error: Acción de formulario desconocida para comentarios.</div>`;
              const backButton = document.createElement('button');
              backButton.className = 'btn btn-secondary mt-3';
              backButton.textContent = '← Volver a Comentarios';
               // Volver a la lista de comentarios del foro actual
              backButton.addEventListener('click', () => this.loadComentariosForoList(this.currentForoId, this.contentArea, this.apiBaseUrl, this.backToForosCallback));
              this.contentArea.appendChild(backButton);
             return;
        }


         try {
             const response = await fetch(url, {
                 method: method,
                 headers: {
                     'Content-Type': 'application/json',
                     // Añadir cabeceras de autenticación si las necesitas
                 },
                 body: JSON.stringify(data) // Enviar datos como JSON
             });

             const result = await response.json(); // Leer la respuesta JSON

             if (!response.ok) {
                 // Manejar errores de la API (ej: validación 400, no encontrado 404, error de DB 500)
                 // Mostrar mensaje de error detallado de la API si está disponible
                const errorMessage = result.message || response.statusText;
                throw new Error(`Error de la API (${response.status}): ${errorMessage}`);
             }

             // Si la operación fue exitosa
             console.log(`Comentario ${action === 'create' ? 'creado' : 'actualizado'} con éxito:`, result);

             // Mostrar un mensaje de éxito temporal
             this.contentArea.innerHTML = `<h2>Gestionar Foros</h2><div class="alert alert-success" role="alert">Comentario ${action === 'create' ? 'creado' : 'actualizado'} con éxito!</div>`;


             // Recargar la lista de comentarios del foro actual después de la operación exitosa
             setTimeout(() => {
                  // Usar el foro ID actual almacenado para recargar la lista de comentarios
                  this.loadComentariosForoList(this.currentForoId, this.contentArea, this.apiBaseUrl, this.backToForosCallback);
             }, 1500); // Esperar 1.5 segundos

        } catch (error) {
            console.error(`Error al ${action} el comentario:`, error);
             // Mostrar el mensaje de error al usuario
             this.contentArea.innerHTML = `<h2>Gestionar Foros</h2><div class="alert alert-danger" role="alert">Error al ${action === 'create' ? 'crear' : 'actualizar'} el comentario: ${error.message}</div>`;
              const backButton = document.createElement('button');
              backButton.className = 'btn btn-secondary mt-3';
              backButton.textContent = 'Volver';
              backButton.addEventListener('click', () => {
                  // Intentar volver al formulario si es edición y el error es de validación,
                  // o solo a la lista si es creación o error general
                  if (action === 'update' && error.message.includes('(400):')) {
                       this.showComentarioForm(comentarioId); // Intentar recargar formulario de edición
                   } else {
                       // Volver a la lista de comentarios del foro actual
                      this.loadComentariosForoList(this.currentForoId, this.contentArea, this.apiBaseUrl, this.backToForosCallback);
                   }
              });
              this.contentArea.appendChild(backButton);
        } finally {
             submitButton.disabled = false;
             submitButton.textContent = originalButtonText;
         }
    },

    /**
     * Envía una petición DELETE para eliminar un comentario.
     * @param {string} comentarioId - El ID del comentario a eliminar.
     */
     deleteComentarioItem: async function(comentarioId) {
         // TODO: Implementar lógica para eliminar comentario (DELETE)
         console.log("Eliminar comentario con ID:", comentarioId);

         // Moverse a la vista de carga
         this.contentArea.innerHTML = `<h2>Gestionar Foros</h2><div class="loading">Eliminando comentario ID ${comentarioId}...</div>`;

          try {
              // URL para eliminar comentario: /api/v1/comentarios/{id}
              const response = await fetch(`${this.apiBaseUrl}/comentarios/${comentarioId}`, {
                  method: 'DELETE',
                  headers: {
                      'Content-Type': 'application/json',
                      // Añadir cabeceras de autenticación si las necesitas
                  }
              });

              let result = {};
              if (response.status !== 204) { // Si no es 204 No Content, intenta leer el JSON
                  result = await response.json();
              }

              if (!response.ok) {
                  const errorMessage = result.message || response.statusText;
                  throw new Error(`Error de la API (${response.status}): ${errorMessage}`);
              }

              // Si la operación fue exitosa
              console.log(`Comentario (ID: ${comentarioId}) eliminado con éxito.`, result);

             // Mostrar un mensaje de éxito temporal
               this.contentArea.innerHTML = `<h2>Gestionar Foros</h2><div class="alert alert-success" role="alert">Comentario (ID: ${comentarioId}) eliminado con éxito!</div>`;


              // Recargar la lista de comentarios del foro actual después de la eliminación exitosa
              setTimeout(() => {
                  // Usar el foro ID actual almacenado para recargar la lista de comentarios
                  this.loadComentariosForoList(this.currentForoId, this.contentArea, this.apiBaseUrl, this.backToForosCallback);
              }, 1500); // Esperar 1.5 segundos

          } catch (error) {
              console.error(`Error al eliminar el comentario (ID: ${comentarioId}):`, error);
              // Mostrar el mensaje de error al usuario
              this.contentArea.innerHTML = `<h2>Gestionar Foros</h2><div class="alert alert-danger" role="alert">Error al eliminar el comentario: ${error.message}</div>`;
               // Opcional: Añadir botón para volver a la lista
               const backButton = document.createElement('button');
               backButton.className = 'btn btn-secondary mt-3';
               backButton.textContent = '← Volver a Comentarios';
               // Volver a la lista de comentarios del foro actual
               backButton.addEventListener('click', () => this.loadComentariosForoList(this.currentForoId, this.contentArea, this.apiBaseUrl, this.backToForosCallback));
               this.contentArea.appendChild(backButton);
          }
    }

    // --- Fin de funciones de Comentarios ---
};