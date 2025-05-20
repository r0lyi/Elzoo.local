// public/js/usuarios.js

// Wrap in an IIFE or block to avoid polluting the global scope
(function() {
    // Ensure this script runs after the DOM is fully loaded
    document.addEventListener('DOMContentLoaded', () => {

        // Get elements specific to the user management component
        const userManagementContainer = document.getElementById('user-management'); // Optional: can scope searches
        if (!userManagementContainer) {
             console.error("User management container not found!");
             return; // Stop if the component isn't on the page
        }

        const userTableBody = userManagementContainer.querySelector('#users-table tbody');
        const addUserBtn = userManagementContainer.querySelector('#add-user-btn');
        const userFormModal = userManagementContainer.querySelector('#user-form-modal');
        const userForm = userManagementContainer.querySelector('#user-form');
        const formTitle = userManagementContainer.querySelector('#form-title');
        const userIdInput = userManagementContainer.querySelector('#user-id');
        const nameInput = userManagementContainer.querySelector('#name');
        const emailInput = userManagementContainer.querySelector('#email');
        const passwordField = userManagementContainer.querySelector('#password-field');
        const passwordInput = userManagementContainer.querySelector('#password');
        const roleInput = userManagementContainer.querySelector('#role');
        const cancelFormBtn = userManagementContainer.querySelector('#cancel-form-btn');
        const messageDiv = userManagementContainer.querySelector('#user-message');

        // Make sure this URL matches your API route defined in routes/api.php
        const API_BASE_URL = '/api/v1/usuarios';

        // --- Helper Functions (Same as before) ---

        function showMessage(text, isError = false) {
            messageDiv.textContent = text;
            messageDiv.style.display = 'block';
            messageDiv.style.color = isError ? 'red' : 'green';
            messageDiv.style.borderColor = isError ? 'red' : 'green';
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        }

        function clearMessage() {
            messageDiv.textContent = '';
            messageDiv.style.display = 'none';
        }

        function showForm(title, userData = null) {
            formTitle.textContent = title;
            userForm.reset();

            if (userData) {
                userIdInput.value = userData.id;
                nameInput.value = userData.nombre;
                emailInput.value = userData.email;
                roleInput.value = userData.rol;
                passwordField.style.display = 'none';
                passwordInput.removeAttribute('required');
            } else {
                userIdInput.value = '';
                passwordField.style.display = 'block';
                passwordInput.setAttribute('required', 'required');
            }

            userFormModal.style.display = 'block';
            clearMessage();
        }

        function hideForm() {
            userFormModal.style.display = 'none';
        }

        // --- API Interaction Functions (Same core logic) ---

        async function fetchUsers() {
            userTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Loading users...</td></tr>';

            try {
                const response = await fetch(API_BASE_URL);
                const users = await response.json();

                userTableBody.innerHTML = '';

                if (response.ok) {
                    if (users.length === 0) {
                        userTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No users found.</td></tr>';
                    } else {
                        users.forEach(user => {
                            const row = `
                                <tr data-id="${user.id}">
                                    <td>${user.id}</td>
                                    <td>${user.nombre}</td>
                                    <td>${user.email}</td>
                                    <td>${user.rol}</td>
                                    <td>
                                        <button class="edit-btn">Edit</button>
                                        <button class="delete-btn">Delete</button>
                                    </td>
                                </tr>
                            `;
                            userTableBody.innerHTML += row;
                        });
                    }
                } else {
                    showMessage(`Error fetching users: ${users.message || response.statusText}`, true);
                    userTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Error loading users.</td></tr>';
                }

            } catch (error) {
                console.error('Fetch error:', error);
                showMessage('An error occurred while fetching users.', true);
                 userTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Network error.</td></tr>';
            }
        }

        async function handleFormSubmit(event) {
            event.preventDefault();

            const id = userIdInput.value;
            const name = nameInput.value.trim();
            const email = emailInput.value.trim();
            const password = passwordInput.value;
            const role = roleInput.value;

            if (!name || !email || !role || (!id && !password)) {
                 showMessage('Please fill in all required fields.', true);
                 return;
            }
             if (!/\S+@\S+\.\S+/.test(email)) {
                 showMessage('Please enter a valid email address.', true);
                 return;
             }


            const userData = {
                nombre: name,
                email: email,
                rol: role
            };

            let url = API_BASE_URL;
            let method = 'POST';

            if (id) {
                url = `${API_BASE_URL}/${id}`;
                method = 'PUT';
            } else {
                 if (!password) { // Double check password for POST
                     showMessage('Password is required for new users.', true);
                     return;
                 }
                 userData.password = password;
            }


            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(userData)
                });

                const result = await response.json();

                if (response.ok) {
                    showMessage(`User ${id ? 'updated' : 'added'} successfully!`, false);
                    hideForm();
                    fetchUsers(); // Refresh the table
                } else {
                    showMessage(`Error ${id ? 'updating' : 'adding'} user: ${result.message || response.statusText}`, true);
                }

            } catch (error) {
                console.error('Form submission error:', error);
                showMessage(`An error occurred while ${id ? 'updating' : 'adding'} the user.`, true);
            }
        }

        async function handleTableClick(event) {
            const target = event.target;

            if (target.classList.contains('edit-btn')) {
                const row = target.closest('tr');
                const userId = row.dataset.id;

                if (userId) {
                     try {
                         const response = await fetch(`${API_BASE_URL}/${userId}`);
                         const user = await response.json();

                         if(response.ok && user) {
                              showForm('Edit User', user);
                         } else {
                              showMessage(`Could not fetch user ${userId} for editing: ${user.message || response.statusText}`, true);
                         }
                     } catch(error) {
                          console.error('Fetch single user error:', error);
                          showMessage(`An error occurred fetching user ${userId} for editing.`, true);
                     }
                }
            }

            if (target.classList.contains('delete-btn')) {
                const row = target.closest('tr');
                const userId = row.dataset.id;
                const userName = row.cells[1].textContent;

                if (userId && confirm(`Are you sure you want to delete user "${userName}" (ID: ${userId})?`)) {
                    try {
                        const response = await fetch(`${API_BASE_URL}/${userId}`, {
                            method: 'DELETE'
                        });

                        const result = await response.json();

                        if (response.ok) {
                            showMessage(`User "${userName}" deleted successfully.`, false);
                            row.remove();
                            if (userTableBody.children.length === 0) {
                                userTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No users found.</td></tr>';
                            }
                        } else {
                            showMessage(`Error deleting user "${userName}": ${result.message || response.statusText}`, true);
                        }

                    } catch (error) {
                        console.error('Delete error:', error);
                        showMessage(`An error occurred while deleting user "${userName}".`, true);
                    }
                }
            }
        }

        // --- Event Listeners ---

        fetchUsers();

        addUserBtn.addEventListener('click', () => {
            showForm('Add User');
        });

        cancelFormBtn.addEventListener('click', hideForm);

        userForm.addEventListener('submit', handleFormSubmit);

        userTableBody.addEventListener('click', handleTableClick);

    });
})(); // End IIFE