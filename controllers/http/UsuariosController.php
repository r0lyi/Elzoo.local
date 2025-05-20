<?php
// api/v1/usuarios/UsuariosController.php

// Ajusta la ruta para que apunte correctamente a tu modelo Usuarios.php
// Si este archivo está en /api/v1/usuarios/, y models/ está en la raíz del proyecto,
// entonces la ruta sería ../../../models/Usuarios.php
require_once __DIR__ . '/../../models/Usuarios.php';

class UsuariosController {


    private function getJsonRequestBody() {
        $input = file_get_contents('php://input');
        $data = json_decode($input);
        if (json_last_error() !== JSON_ERROR_NONE) {
             // Manejar error de JSON inválido
             http_response_code(400); // Bad Request
             echo json_encode(["message" => "Solicitud JSON inválida. Error: " . json_last_error_msg()]);
             return null;
        }
        return $data;
    }

  
    private function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data);
    }

  
    private function sendErrorResponse($message, $statusCode = 500) {
        http_response_code($statusCode);
        echo json_encode(["message" => $message]);
    }


    // Maneja la solicitud GET /api/v1/usuarios
    public function index() {
        $usuarios = Usuarios::findAll();

        // Omitir campos sensibles como la password y el token en la respuesta
        $safeUsuarios = array_map(function($user) {
            unset($user['password']);
            unset($user['token']); // Omitir la columna token si existe y no quieres mostrarla
            return $user;
        }, $usuarios);

        $this->sendJsonResponse($safeUsuarios);
    }

    // Maneja la solicitud GET /api/v1/usuarios/{id}
    public function show($id) {
        // Validar que el ID sea un número entero positivo
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de usuario inválido. Debe ser un número entero positivo.", 400);
            return;
        }

        // Usamos obtenerPorId porque ya selecciona los campos seguros
        $usuario = Usuarios::obtenerPorId($id);

        if ($usuario) {
            $this->sendJsonResponse($usuario);
        } else {
            $this->sendErrorResponse("Usuario no encontrado.", 404);
        }
    }

    // Maneja la solicitud POST /api/v1/usuarios
    public function store() {
        $data = $this->getJsonRequestBody();

        if ($data === null) {
            return; // getJsonRequestBody ya maneja el error
        }

        // Validar datos de entrada (requeridos)
        $requiredFields = ['nombre', 'email', 'password', 'rol'];
        foreach ($requiredFields as $field) {
            // Usamos isset y también verificamos que no estén vacíos después de trim
            if (!isset($data->$field) || trim($data->$field) === '') {
                 $this->sendErrorResponse("El campo '" . $field . "' es requerido y no puede estar vacío.", 400);
                 return;
            }
        }

        // Validación básica de email
         if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
             $this->sendErrorResponse("Formato de email inválido.", 400);
             return;
         }

        // Validación básica del rol
         $allowedRoles = ['admin', 'usuario']; // Ajusta según tus ENUM en la BD
         if (!in_array($data->rol, $allowedRoles)) {
             $this->sendErrorResponse("Rol inválido. Los roles permitidos son: " . implode(', ', $allowedRoles) . ".", 400);
             return;
         }

        // Verificar si el correo ya existe
        if (Usuarios::verificarCorreoExistente($data->email)) {
            $this->sendErrorResponse("El correo electrónico ya está registrado.", 409); // 409 Conflict es más apropiado
            return;
        }

        // --- ¡¡IMPORTANTE!! HASHEAR LA PASSWORD ---
        // NUNCA guardes contraseñas en texto plano.
        $hashedPassword = password_hash($data->password, PASSWORD_DEFAULT); // Usa un algoritmo seguro

        if ($hashedPassword === false) {
             $this->sendErrorResponse("Error interno al procesar la contraseña.", 500);
             return;
        }

        $userData = [
            'nombre' => trim($data->nombre),
            'email' => trim($data->email),
            'password' => $hashedPassword, // <-- ¡PASSWORD HASHEADA!
            'rol' => trim($data->rol)
        ];

        $newUserId = Usuarios::create($userData);

        if ($newUserId !== false) {
            // Obtener los datos del usuario recién creado para la respuesta (sin password/token)
             $newUser = Usuarios::obtenerPorId($newUserId);
             $this->sendJsonResponse($newUser, 201); // 201 Created
        } else {
            $this->sendErrorResponse("Error al crear el usuario en la base de datos.", 500);
        }
    }

    // Maneja la solicitud PUT /api/v1/usuarios/{id}
    public function update($id) {
        // Validar que el ID sea un número entero positivo
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de usuario inválido para actualizar.", 400);
            return;
        }

        $data = $this->getJsonRequestBody();

         if ($data === null) {
            return; // getJsonRequestBody ya maneja el error
        }

        // Validar que existan al menos algunos campos para actualizar
        // Aceptamos nombre, email o rol para actualizar
        if (!isset($data->nombre) && !isset($data->email) && !isset($data->rol)) {
             $this->sendErrorResponse("Se requieren campos para actualizar (nombre, email, rol).", 400);
             return;
        }

         // Validar campos si están presentes
         $updateData = [];
         if (isset($data->nombre)) {
             if (trim($data->nombre) === '') {
                  $this->sendErrorResponse("El campo 'nombre' no puede estar vacío.", 400);
                  return;
             }
             $updateData['nombre'] = trim($data->nombre);
         }
         if (isset($data->email)) {
             if (trim($data->email) === '' || !filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
                 $this->sendErrorResponse("Formato de email inválido o vacío.", 400);
                 return;
             }
              // TODO: Verificar si el nuevo email ya existe para OTRO usuario (no el que estamos actualizando)
             $updateData['email'] = trim($data->email);
         }
         if (isset($data->rol)) {
              $allowedRoles = ['admin', 'usuario']; // Ajusta según tus ENUM
              if (!in_array($data->rol, $allowedRoles)) {
                 $this->sendErrorResponse("Rol inválido. Los roles permitidos son: " . implode(', ', $allowedRoles) . ".", 400);
                 return;
              }
             $updateData['rol'] = trim($data->rol);
         }

        // Verificar si el usuario existe antes de intentar actualizar
        // Usamos find() porque devuelve el array completo o false
        if (!Usuarios::find($id)) {
             $this->sendErrorResponse("Usuario no encontrado para actualizar.", 404);
             return;
        }

        // No se permite actualizar la password directamente con este método PUT
        // Si necesitas actualizar password, crea un endpoint y método separado (ej: PUT /api/v1/usuarios/{id}/password)
        // que requiera la password actual y la nueva password, y hashee la nueva.


        $success = Usuarios::update($id, $updateData);

        if ($success) {
            // Retornar los datos del usuario actualizado o un mensaje de éxito
             $updatedUser = Usuarios::obtenerPorId($id); // Obtener los datos seguros
            $this->sendJsonResponse($updatedUser, 200);
        } else {
             // Podría ser que no hubo error de DB pero tampoco se afectaron filas (si $updateData estaba vacío)
             // Sin embargo, ya validamos que $updateData no esté vacío.
            $this->sendErrorResponse("Error al actualizar el usuario en la base de datos.", 500);
        }
    }

  
    // Maneja la solicitud DELETE /api/v1/usuarios/{id}
    public function destroy($id) {
         // Validar que el ID sea un número entero positivo
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de usuario inválido para eliminar.", 400);
            return;
        }

         $userToDelete = Usuarios::find($id); // Usamos find() para obtener el usuario si existe
         if (!$userToDelete) {
              $this->sendErrorResponse("Usuario no encontrado para eliminar.", 404);
              return;
         }

        try {
            $connection = ControllerDatabase::connect(); // Obtener la conexión a la base de datos

            $queryForos = "DELETE FROM foros WHERE autor_id = ?";
            $stmtForos = $connection->prepare($queryForos);
            $stmtForos->execute([$id]); // Ejecuta la eliminación de posts de foro
            // Si hay un error de DB aquí, la PDOException lo capturará abajo.
            // Si el usuario no tiene posts, esto simplemente afectará 0 filas, lo cual es normal.

            // --- PASO 2: Eliminar comentarios del usuario ---
            // La base de datos impide eliminar un usuario si tiene comentarios.
            // Esto maneja los comentarios que el usuario haya escrito EN CUALQUIER POST.
            $queryComments = "DELETE FROM comentarios_foro WHERE autor_id = ?";
            $stmtComments = $connection->prepare($queryComments);
            $stmtComments->execute([$id]); // Ejecuta la eliminación de comentarios

            $success = Usuarios::delete($id); // Llama al método estático DELETE del modelo Usuarios

            if ($success) {
                // Si la eliminación del usuario fue exitosa.
                // Actualizar el mensaje de éxito para reflejar que se manejaron posts y comentarios.
                $this->sendJsonResponse(["message" => "Usuario eliminado con éxito, incluyendo sus posts de foro y comentarios asociados (si los tenía)."], 200);
            } else {
                // Esto podría ocurrir si Usuarios::delete fallara por alguna otra razón de DB.
                $this->sendErrorResponse("Error desconocido al eliminar el usuario principal.", 500);
            }

        } catch (PDOException $e) { // Captura cualquier error de base de datos
            // Loggear el error real de la base de datos.
            // Actualizar el mensaje del log para reflejar que se intentaron eliminar posts y comentarios.
            error_log("Database error during user, foro, or comment deletion (ID: {$id}): " . $e->getMessage());

            // Envía una respuesta de error genérica al cliente de la API.
            // Actualizar el mensaje de error para el usuario.
            $this->sendErrorResponse("Error de base de datos al intentar eliminar el usuario, sus posts de foro y comentarios.", 500);

        } catch (Exception $e) { // Captura otros posibles errores generales
             // Loggear el error general.
            error_log("Unexpected error during user deletion (ID: {$id}): " . $e->getMessage());
             // Mantener el mensaje de error general para el usuario.
             $this->sendErrorResponse("Ocurrió un error inesperado al eliminar al usuario.", 500);
        }
    }
}