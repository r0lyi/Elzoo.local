<?php

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
            return;
        }

        // Validar datos de entrada (requeridos)
        $requiredFields = ['nombre', 'email', 'password', 'rol'];
        foreach ($requiredFields as $field) {
            if (!isset($data->$field) || trim($data->$field) === '') {
                 $this->sendErrorResponse("El campo '" . $field . "' es requerido y no puede estar vacío.", 400);
                 return;
            }
        }

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

     public function changePassword($id) {
        // Validar que el ID sea un número entero positivo
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de usuario inválido para cambiar contraseña.", 400);
            return;
        }

        $data = $this->getJsonRequestBody();

        if ($data === null) {
            return; // getJsonRequestBody ya maneja el error
        }

        // Validar que la nueva contraseña esté presente y no esté vacía
        if (!isset($data->new_password) || trim($data->new_password) === '') {
            $this->sendErrorResponse("El campo 'new_password' es requerido y no puede estar vacío.", 400);
            return;
        }

        if (!Usuarios::find($id)) {
            $this->sendErrorResponse("Usuario no encontrado para cambiar contraseña.", 404);
            return;
        }

        // Hashear la nueva contraseña
        $hashedPassword = password_hash(trim($data->new_password), PASSWORD_DEFAULT);

        if ($hashedPassword === false) {
             $this->sendErrorResponse("Error interno al procesar la nueva contraseña.", 500);
             return;
        }

        $success = Usuarios::updatePassword($id, $hashedPassword);

        if ($success) {
            $this->sendJsonResponse(["message" => "Contraseña actualizada con éxito para el usuario ID: {$id}."], 200);
        } else {
            $this->sendErrorResponse("Error al actualizar la contraseña en la base de datos.", 500);
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
            return; 
        }

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

        if (!Usuarios::find($id)) {
             $this->sendErrorResponse("Usuario no encontrado para actualizar.", 404);
             return;
        }

    


        $success = Usuarios::update($id, $updateData);

        if ($success) {
             $updatedUser = Usuarios::obtenerPorId($id); // Obtener los datos seguros
            $this->sendJsonResponse($updatedUser, 200);
        } else {
          
            $this->sendErrorResponse("Error al actualizar el usuario en la base de datos.", 500);
        }
    }

  
    // Maneja la solicitud DELETE /api/v1/usuarios/{id}
    public function destroy($id) {
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de usuario inválido para eliminar.", 400);
            return;
        }

         $userToDelete = Usuarios::find($id); 
         if (!$userToDelete) {
              $this->sendErrorResponse("Usuario no encontrado para eliminar.", 404);
              return;
         }

        // --- Bloque try...catch para manejar errores de DB ---
        try {
            $connection = ControllerDatabase::connect(); // Obtener la conexión a la base de datos

            $queryForos = "DELETE FROM foros WHERE autor_id = ?";
            $stmtForos = $connection->prepare($queryForos);
            $stmtForos->execute([$id]); // Ejecuta la eliminación de posts de foro

            $queryComments = "DELETE FROM comentarios_foro WHERE autor_id = ?";
            $stmtComments = $connection->prepare($queryComments);
            $stmtComments->execute([$id]); // Ejecuta la eliminación de comentarios
           


            $success = Usuarios::delete($id); 

            if ($success) {
                
                $this->sendJsonResponse(["message" => "Usuario eliminado con éxito, incluyendo sus posts de foro y comentarios asociados (si los tenía)."], 200);
            } else {
                $this->sendErrorResponse("Error desconocido al eliminar el usuario principal.", 500);
            }

        } catch (PDOException $e) { 
            
            error_log("Database error during user, foro, or comment deletion (ID: {$id}): " . $e->getMessage());

           
            $this->sendErrorResponse("Error de base de datos al intentar eliminar el usuario, sus posts de foro y comentarios.", 500);

        } catch (Exception $e) { // Captura otros posibles errores generales
             // Loggear el error general.
            error_log("Unexpected error during user deletion (ID: {$id}): " . $e->getMessage());
             $this->sendErrorResponse("Ocurrió un error inesperado al eliminar al usuario.", 500);
        }
    }
}