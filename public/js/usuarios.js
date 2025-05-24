// public/js/usuarios.js

document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const usersTableBody = document.getElementById('usersTableBody');
    const userAlertMessage = document.getElementById('user-alert-message');
    const refreshUsersBtn = document.getElementById('refreshUsersBtn');
    const addUserForm = document.getElementById('addUserForm');
    const editUserForm = document.getElementById('editUserForm');
    const changePasswordForm = document.getElementById('changePasswordForm'); // Nuevo
    const confirmDeleteUserBtn = document.getElementById('confirmDeleteUserBtn');

    // Modals de Bootstrap (instancias para controlar su visibilidad)
    const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
    const editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
    const changePasswordModal = new bootstrap.Modal(document.getElementById('changePasswordModal')); // Nuevo
    const deleteUserModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));

    // Función para mostrar mensajes de alerta
    function showAlert(message, type = 'success') {
        userAlertMessage.textContent = message;
        userAlertMessage.className = `alert alert-${type} d-block`; // Mostrar y aplicar estilo
        setTimeout(() => {
            userAlertMessage.classList.add('d-none'); // Ocultar después de 5 segundos
        }, 5000);
    }

    // Función para renderizar la tabla de usuarios
    function renderUsersTable(users) {
        usersTableBody.innerHTML = ''; // Limpiar la tabla

        if (users.length === 0) {
            usersTableBody.innerHTML = '<tr><td colspan="5" class="text-center p-4">No hay usuarios registrados.</td></tr>';
            return;
        }

        users.forEach(user => {
            const row = `
                <tr>
                    <td>${user.id}</td>
                    <td>${user.nombre}</td>
                    <td>${user.email}</td>
                    <td>${user.rol}</td>
                    <td>
                        <button class="btn btn-sm btn-info me-2 edit-user-btn" data-id="${user.id}" data-name="${user.nombre}" data-email="${user.email}" data-rol="${user.rol}">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-sm btn-warning me-2 change-password-btn" data-id="${user.id}" data-name="${user.nombre}">
                            <i class="fas fa-key"></i> Contraseña
                        </button>
                        <button class="btn btn-sm btn-danger delete-user-btn" data-id="${user.id}" data-name="${user.nombre}" data-email="${user.email}">
                            <i class="fas fa-trash-alt"></i> Eliminar
                        </button>
                    </td>
                </tr>
            `;
            usersTableBody.insertAdjacentHTML('beforeend', row);
        });

        // Adjuntar eventos a los nuevos botones
        attachTableButtonListeners();
    }

    // Función para cargar usuarios desde la API
    async function loadUsers() {
        usersTableBody.innerHTML = '<tr><td colspan="5" class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i> Cargando usuarios...</td></tr>';
        try {
            const response = await fetch('/api/v1/usuarios'); // Tu endpoint de la API
            const data = await response.json();
            if (response.ok) {
                renderUsersTable(data);
            } else {
                showAlert(`Error al cargar usuarios: ${data.message || response.statusText}`, 'danger');
                usersTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger p-4">Error al cargar los usuarios.</td></tr>';
            }
        } catch (error) {
            console.error('Error fetching users:', error);
            showAlert('Error de conexión al cargar usuarios.', 'danger');
            usersTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger p-4">Error de conexión.</td></tr>';
        }
    }

    // Función para añadir un nuevo usuario
    addUserForm.addEventListener('submit', async function(event) {
        event.preventDefault();

        const nombre = document.getElementById('addUserName').value;
        const email = document.getElementById('addUserEmail').value;
        const password = document.getElementById('addUserPassword').value;
        const rol = document.getElementById('addUserRol').value;

        try {
            const response = await fetch('/api/v1/usuarios', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ nombre, email, password, rol })
            });

            const data = await response.json();

            if (response.ok) {
                showAlert('Usuario añadido con éxito.', 'success');
                addUserModal.hide();
                addUserForm.reset();
                loadUsers();
            } else {
                showAlert(`Error al añadir usuario: ${data.message || 'Error desconocido.'}`, 'danger');
            }
        } catch (error) {
            console.error('Error adding user:', error);
            showAlert('Error de conexión al añadir usuario.', 'danger');
        }
    });

    // Función para editar un usuario existente (AHORA SIN CAMBIO DE CONTRASEÑA)
    editUserForm.addEventListener('submit', async function(event) {
        event.preventDefault();

        const id = document.getElementById('editUserId').value;
        const nombre = document.getElementById('editUserName').value;
        const email = document.getElementById('editUserEmail').value;
        const rol = document.getElementById('editUserRol').value;

        const updateData = { nombre, email, rol };

        try {
            const response = await fetch(`/api/v1/usuarios/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(updateData)
            });

            const data = await response.json();

            if (response.ok) {
                showAlert('Usuario actualizado con éxito.', 'success');
                editUserModal.hide();
                loadUsers();
            } else {
                showAlert(`Error al actualizar usuario: ${data.message || 'Error desconocido.'}`, 'danger');
            }
        } catch (error) {
            console.error('Error updating user:', error);
            showAlert('Error de conexión al actualizar usuario.', 'danger');
        }
    });

    // NUEVA FUNCIÓN: Cambiar contraseña de usuario
    changePasswordForm.addEventListener('submit', async function(event) {
        event.preventDefault();

        const id = document.getElementById('changePasswordUserId').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmNewPassword = document.getElementById('confirmNewPassword').value;

        if (newPassword !== confirmNewPassword) {
            showAlert('Las contraseñas no coinciden.', 'warning');
            return;
        }
        if (newPassword.length < 6) { // Ejemplo de validación de longitud
            showAlert('La contraseña debe tener al menos 6 caracteres.', 'warning');
            return;
        }

        try {
            const response = await fetch(`/api/v1/usuarios/${id}/password`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ password: newPassword })
            });

            const data = await response.json();

            if (response.ok) {
                showAlert('Contraseña actualizada con éxito.', 'success');
                changePasswordModal.hide();
                changePasswordForm.reset(); // Limpiar formulario
            } else {
                showAlert(`Error al cambiar contraseña: ${data.message || 'Error desconocido.'}`, 'danger');
            }
        } catch (error) {
            console.error('Error changing password:', error);
            showAlert('Error de conexión al cambiar la contraseña.', 'danger');
        }
    });


    // Función para eliminar un usuario (sin cambios)
    confirmDeleteUserBtn.addEventListener('click', async function() {
        const id = document.getElementById('deleteUserIdConfirm').value;

        try {
            const response = await fetch(`/api/v1/usuarios/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok) {
                showAlert(data.message || 'Usuario eliminado con éxito.', 'success');
                deleteUserModal.hide();
                loadUsers();
            } else {
                showAlert(`Error al eliminar usuario: ${data.message || 'Error desconocido.'}`, 'danger');
            }
        } catch (error) {
            console.error('Error deleting user:', error);
            showAlert('Error de conexión al eliminar usuario.', 'danger');
        }
    });

    // Delegación de eventos para botones de la tabla (Editar, Contraseña y Eliminar)
    function attachTableButtonListeners() {
        usersTableBody.querySelectorAll('.edit-user-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const nombre = this.dataset.name;
                const email = this.dataset.email;
                const rol = this.dataset.rol;

                // Rellenar el modal de edición
                document.getElementById('editUserId').value = id;
                document.getElementById('editUserName').value = nombre;
                document.getElementById('editUserEmail').value = email;
                document.getElementById('editUserRol').value = rol;

                editUserModal.show();
            });
        });

        // NUEVO: Event listener para el botón de "Contraseña"
        usersTableBody.querySelectorAll('.change-password-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const nombre = this.dataset.name;

                // Rellenar el modal de cambio de contraseña
                document.getElementById('changePasswordUserId').value = id;
                document.getElementById('changePasswordUserName').textContent = nombre;
                document.getElementById('newPassword').value = ''; // Limpiar campos
                document.getElementById('confirmNewPassword').value = '';

                changePasswordModal.show();
            });
        });

        usersTableBody.querySelectorAll('.delete-user-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const nombre = this.dataset.name;
                const email = this.dataset.email;

                // Rellenar el modal de confirmación
                document.getElementById('deleteUserIdConfirm').value = id;
                document.getElementById('deleteUserNameConfirm').textContent = nombre;
                document.getElementById('deleteUserEmailConfirm').textContent = email;

                deleteUserModal.show();
            });
        });
    }

    // Evento para el botón de refrescar (sin cambios)
    refreshUsersBtn.addEventListener('click', loadUsers);

    // Búsqueda (sin cambios)
    const userSearchInput = document.getElementById('userSearchInput');
    const userSearchBtn = document.getElementById('userSearchBtn');

    userSearchBtn.addEventListener('click', function() {
        const searchTerm = userSearchInput.value.toLowerCase();
        const rows = usersTableBody.querySelectorAll('tr');
        rows.forEach(row => {
            const userName = row.children[1].textContent.toLowerCase();
            const userEmail = row.children[2].textContent.toLowerCase();
            if (userName.includes(searchTerm) || userEmail.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    userSearchInput.addEventListener('keyup', function() {
        if (this.value.length > 2 || this.value.length === 0) {
            userSearchBtn.click();
        }
    });


    // Cargar usuarios cuando el script se ejecute (cuando el componente es visible)
    // Esto es crucial para la primera carga del componente o cuando se inyecta vía AJAX
    loadUsers();
});