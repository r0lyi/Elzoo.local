// public/js/usuarios.js

document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = '/api/v1/usuarios'; // Asegúrate de que esta URL sea correcta para tu API

    // Elementos del formulario principal
    const userForm = document.getElementById('user-form');
    const userIdField = document.getElementById('user-id');
    const nombreField = document.getElementById('nombre');
    const emailField = document.getElementById('email');
    const passwordField = document.getElementById('password'); // Campo de contraseña para CREAR
    const rolField = document.getElementById('rol');

    // Botones y títulos del formulario principal
    const saveUserBtn = document.getElementById('save-user-btn');
    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    const formTitleText = document.getElementById('form-title-text');

    // Elementos de la tabla
    const usersTableBody = document.querySelector('#users-table tbody');
    const noUsersMessage = document.getElementById('no-users-message');

    // Área de mensajes principal
    const messageArea = document.getElementById('message-area');

    // Elementos del Modal de Cambio de Contraseña
    const changePasswordModal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
    const modalUserName = document.getElementById('modal-user-name');
    const modalUserId = document.getElementById('modal-user-id');
    const newPasswordField = document.getElementById('newPassword');
    const confirmNewPasswordField = document.getElementById('confirmNewPassword');
    const saveNewPasswordBtn = document.getElementById('saveNewPasswordBtn');
    const passwordModalMessageArea = document.getElementById('password-modal-message-area');

    let editingUserId = null; // Variable para controlar si estamos editando o creando
    let currentUserIdForPasswordChange = null; // Para almacenar el ID del usuario cuyo password se va a cambiar

    // --- Funciones de Utilidad ---

    function showMessage(message, type = 'success', targetArea = messageArea) {
        targetArea.textContent = message;
        targetArea.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-info');
        targetArea.classList.add('animate__animated', 'animate__fadeIn');

        if (type === 'success') {
            targetArea.classList.add('alert-success', 'animate__bounceIn');
        } else if (type === 'error') {
            targetArea.classList.add('alert-danger', 'animate__shakeX');
        } else {
            targetArea.classList.add('alert-info');
        }
        
        setTimeout(() => {
            targetArea.classList.remove('animate__fadeIn', 'animate__bounceIn', 'animate__shakeX');
            targetArea.classList.add('animate__fadeOut');
            targetArea.addEventListener('animationend', () => {
                targetArea.classList.add('d-none');
                targetArea.classList.remove('animate__fadeOut');
            }, { once: true });
        }, 5000);
    }

    function clearForm() {
        userIdField.value = '';
        nombreField.value = '';
        emailField.value = '';
        passwordField.value = '';
        passwordField.removeAttribute('disabled');
        passwordField.setAttribute('placeholder', 'Dejar vacío para no cambiar al editar');
        rolField.value = 'usuario';

        formTitleText.textContent = 'Crear Nuevo Usuario';
        saveUserBtn.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Usuario';
        saveUserBtn.classList.remove('btn-success');
        saveUserBtn.classList.add('btn-primary');
        cancelEditBtn.classList.add('d-none');
        
        editingUserId = null;
    }

    // --- Peticiones a la API ---

    async function fetchUsers() {
        try {
            const response = await fetch(API_BASE_URL);
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Error al obtener usuarios');
            }
            const users = await response.json();
            renderUsers(users);
        } catch (error) {
            console.error('Error al obtener usuarios:', error);
            showMessage(`Error al cargar usuarios: ${error.message}`, 'error');
            usersTableBody.innerHTML = '';
            noUsersMessage.classList.remove('d-none');
        }
    }

    async function fetchUserById(id) {
        try {
            const response = await fetch(`${API_BASE_URL}/${id}`);
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Error al obtener usuario');
            }
            const user = await response.json();
            
            userIdField.value = user.id;
            nombreField.value = user.nombre;
            emailField.value = user.email;
            rolField.value = user.rol;
            passwordField.value = '';
            passwordField.setAttribute('disabled', 'true'); // Deshabilitar campo password para edición
            
            formTitleText.textContent = `Editar Usuario (ID: ${user.id})`;
            saveUserBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Actualizar Usuario';
            saveUserBtn.classList.remove('btn-primary');
            saveUserBtn.classList.add('btn-success');
            cancelEditBtn.classList.remove('d-none');
            
            editingUserId = id;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (error) {
            console.error('Error al obtener usuario por ID:', error);
            showMessage(`Error al cargar datos del usuario: ${error.message}`, 'error');
            clearForm();
        }
    }

    async function saveUser(event) {
        event.preventDefault();

        const userData = {
            nombre: nombreField.value.trim(),
            email: emailField.value.trim(),
            rol: rolField.value.trim(),
        };

        let method = 'POST';
        let url = API_BASE_URL;

        if (editingUserId) {
            method = 'PUT';
            url = `${API_BASE_URL}/${editingUserId}`;
        } else {
            if (passwordField.value.trim() === '') {
                showMessage('La contraseña es requerida para crear un nuevo usuario.', 'error');
                return;
            }
            userData.password = passwordField.value.trim();
        }

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(userData),
            });

            const responseData = await response.json();

            if (!response.ok) {
                throw new Error(responseData.message || 'Error al guardar usuario');
            }

            showMessage(responseData.message || `Usuario ${editingUserId ? 'actualizado' : 'creado'} con éxito.`, 'success');
            clearForm();
            fetchUsers();
        } catch (error) {
            console.error('Error al guardar usuario:', error);
            showMessage(`Error al guardar usuario: ${error.message}`, 'error');
        }
    }

    async function deleteUser(id) {
        if (!confirm('¿Estás seguro de que quieres eliminar este usuario? Esta acción es irreversible y eliminará también sus posts y comentarios.')) {
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}/${id}`, {
                method: 'DELETE',
            });

            const responseData = await response.json();

            if (!response.ok) {
                throw new Error(responseData.message || 'Error al eliminar usuario');
            }

            showMessage(responseData.message || 'Usuario eliminado con éxito.', 'success');
            fetchUsers();
        } catch (error) {
            console.error('Error al eliminar usuario:', error);
            showMessage(`Error al eliminar usuario: ${error.message}`, 'error');
        }
    }

    // --- NUEVA FUNCIÓN: Cambiar contraseña ---
    async function changeUserPassword() {
        const newPassword = newPasswordField.value.trim();
        const confirmNewPassword = confirmNewPasswordField.value.trim();

        if (newPassword === '' || confirmNewPassword === '') {
            showMessage('Ambos campos de contraseña son requeridos.', 'error', passwordModalMessageArea);
            return;
        }
        if (newPassword !== confirmNewPassword) {
            showMessage('Las contraseñas no coinciden.', 'error', passwordModalMessageArea);
            return;
        }
        if (newPassword.length < 6) { // Ejemplo de validación básica
            showMessage('La contraseña debe tener al menos 6 caracteres.', 'error', passwordModalMessageArea);
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}/${currentUserIdForPasswordChange}/password`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ new_password: newPassword }),
            });

            const responseData = await response.json();

            if (!response.ok) {
                throw new Error(responseData.message || 'Error al cambiar la contraseña');
            }

            showMessage(responseData.message || 'Contraseña actualizada con éxito.', 'success', passwordModalMessageArea);
            
            // Cerrar el modal después de un breve retraso para que el usuario vea el mensaje
            setTimeout(() => {
                changePasswordModal.hide();
                newPasswordField.value = '';
                confirmNewPasswordField.value = '';
                passwordModalMessageArea.classList.add('d-none'); // Ocultar mensaje del modal
            }, 1500);

        } catch (error) {
            console.error('Error al cambiar la contraseña:', error);
            showMessage(`Error: ${error.message}`, 'error', passwordModalMessageArea);
        }
    }
    // ----------------------------------------

    // --- Renderizado de la Tabla ---

    function renderUsers(users) {
        usersTableBody.innerHTML = '';
        if (users.length === 0) {
            noUsersMessage.classList.remove('d-none');
            return;
        }
        noUsersMessage.classList.add('d-none');

        users.forEach(user => {
            const row = usersTableBody.insertRow();
            row.insertCell().textContent = user.id;
            row.insertCell().textContent = user.nombre;
            row.insertCell().textContent = user.email;
            row.insertCell().textContent = user.rol;

            const actionsCell = row.insertCell();
            actionsCell.classList.add('text-center');

            const editButton = document.createElement('button');
            editButton.innerHTML = '<i class="fas fa-edit"></i>';
            editButton.classList.add('btn', 'btn-warning', 'btn-sm', 'me-2');
            editButton.setAttribute('title', 'Editar Usuario');
            editButton.addEventListener('click', () => fetchUserById(user.id));
            actionsCell.appendChild(editButton);

            // Botón para cambiar contraseña
            const changePasswordButton = document.createElement('button');
            changePasswordButton.innerHTML = '<i class="fas fa-key"></i>'; // Icono de llave
            changePasswordButton.classList.add('btn', 'btn-info', 'btn-sm', 'me-2'); // btn-info para este
            changePasswordButton.setAttribute('title', 'Cambiar Contraseña');
            changePasswordButton.addEventListener('click', () => {
                currentUserIdForPasswordChange = user.id; // Almacenar el ID
                modalUserName.textContent = user.nombre; // Mostrar nombre en modal
                modalUserId.textContent = user.id; // Mostrar ID en modal
                newPasswordField.value = ''; // Limpiar campos del modal
                confirmNewPasswordField.value = '';
                passwordModalMessageArea.classList.add('d-none'); // Ocultar mensajes previos del modal
                changePasswordModal.show(); // Mostrar el modal
            });
            actionsCell.appendChild(changePasswordButton);

            const deleteButton = document.createElement('button');
            deleteButton.innerHTML = '<i class="fas fa-trash-alt"></i>';
            deleteButton.classList.add('btn', 'btn-danger', 'btn-sm');
            deleteButton.setAttribute('title', 'Eliminar Usuario');
            deleteButton.addEventListener('click', () => deleteUser(user.id));
            actionsCell.appendChild(deleteButton);
        });
    }

    // --- Event Listeners ---

    userForm.addEventListener('submit', saveUser);
    cancelEditBtn.addEventListener('click', clearForm);
    saveNewPasswordBtn.addEventListener('click', changeUserPassword); // Evento para el botón del modal

    // Cargar usuarios al cargar la página
    fetchUsers();
});