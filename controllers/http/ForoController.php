<?php
// controllers/http/ForoController.php

// Rutas relativas desde controllers/http/
// Sube dos niveles (../..) a la raíz, luego baja a models/
require_once __DIR__ . '/../../models/Foro.php'; // Para usar el modelo Foro
require_once __DIR__ . '/../../models/Usuarios.php'; // Necesario para validar autor_id y obtener info del autor
// Potencialmente incluir ComentarioForo si quieres añadir conteo de comentarios o comentarios en la respuesta GET
require_once __DIR__ . '/../../models/ComentarioForo.php'; // Incluido para poder eliminar comentarios asociados en destroy
// Sube un nivel (../) a controllers/ para el controlador de base de datos (buena práctica incluirlo)
require_once __DIR__ . '/../ControllerDatabase.php';

class ForoController {

    // --- Métodos de ayuda (copiados de UsuariosController o un controlador base) ---
    // Si ya tienes un archivo de helpers o un controlador base con estos métodos, úsalos.
    // De lo contrario, copia estas funciones aquí:

     /**
     * Obtiene el cuerpo de la solicitud JSON.
     * @return object|null Objeto PHP si es JSON válido, null en caso contrario.
     */
    private function getJsonRequestBody() {
        $input = file_get_contents('php://input');
        $data = json_decode($input);
        if (json_last_error() !== JSON_ERROR_NONE) {
             http_response_code(400); // Bad Request
             echo json_encode(["message" => "Solicitud JSON inválida. Error: " . json_last_error_msg()]);
             return null;
        }
        return $data;
    }

    /**
     * Envía una respuesta JSON.
     * @param mixed $data Datos a codificar en JSON.
     * @param int $statusCode Código de estado HTTP.
     */
    private function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data);
    }

     /**
     * Envía una respuesta de error JSON.
     * @param string $message Mensaje de error.
     * @param int $statusCode Código de estado HTTP.
     */
    private function sendErrorResponse($message, $statusCode = 500) {
        http_response_code($statusCode);
        echo json_encode(["message" => $message]);
    }

    // --- Métodos del Controlador API para Foros ---

    // GET /api/v1/foros
    public function index() {
        // Obtener todos los posts de foro, incluyendo datos básicos del autor
        $foros = Foro::findAllWithAuthor(); // O Foro::findAll() si no necesitas info del autor

        // Opcional: Podrías añadir el conteo de comentarios a cada post aquí
        // $forosWithCounts = array_map(function($foro) {
        //     // Esto requeriría añadir un método como countByForoId($foro['id']) en ComentarioForo
        //     $foro['comment_count'] = ComentarioForo::countByForoId($foro['id']);
        //     return $foro;
        // }, $foros);
        // $this->sendJsonResponse($forosWithCounts);

        $this->sendJsonResponse($foros); // Enviar la lista de foros
    }

    // GET /api/v1/foros/{id}
    public function show($id) {
         // Validar ID del foro
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de foro inválido.", 400);
            return;
        }

        // Usar el método del modelo para encontrar el post de foro, incluyendo datos básicos del autor
        $foro = Foro::findWithAuthor($id); // O Foro::find($id) si no necesitas info del autor

        if ($foro) {
            // Opcional: Podrías incrustar los comentarios en la respuesta del post,
            // pero es más común usar un endpoint separado como /api/v1/foros/{id}/comentarios
            // $foro['comentarios'] = ComentarioForo::findByForoIdWithAuthor($id); // Esto requeriría ComentarioForo model include y ComentarioForoController ya lo hace

            $this->sendJsonResponse($foro); // Enviar los datos del post de foro
        } else {
            $this->sendErrorResponse("Post de foro no encontrado.", 404);
        }
    }


    // POST /api/v1/foros
    // Requiere cuerpo JSON con 'titulo', 'contenido', 'autor_id'
    public function store() {
         $data = $this->getJsonRequestBody();

         if ($data === null) {
             return; // getJsonRequestBody maneja error de JSON inválido
         }

         // Validar campos requeridos en el cuerpo JSON
         $requiredFields = ['titulo', 'contenido', 'autor_id'];
         foreach ($requiredFields as $field) {
             if (!isset($data->$field) || trim($data->$field) === '') {
                  $this->sendErrorResponse("El campo '" . $field . "' es requerido y no puede estar vacío.", 400);
                  return;
             }
         }

         // Validar formato y existencia del autor_id (¡importante!)
         $autorId = (int) $data->autor_id; // Asegurarse de que sea un entero
         if (!filter_var($autorId, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
              $this->sendErrorResponse("ID de autor inválido.", 400);
              return;
         }
         // Verificar si el autor realmente existe en la tabla usuarios
         if (!Usuarios::find($autorId)) { // Usamos Usuarios::find del modelo Usuarios
              $this->sendErrorResponse("El autor con ID " . $autorId . " no existe.", 404); // O 400
              return;
         }


         // Preparar los datos para el método create del modelo Foro
         $foroData = [
             'titulo' => trim($data->titulo),
             'contenido' => trim($data->contenido),
             'autor_id' => $autorId // Viene del cuerpo JSON (validado)
         ];

         // Usar el método del modelo para crear el post de foro
         $newForoId = Foro::create($foroData);

         if ($newForoId !== false) {
             // Si fue exitoso, obtener los datos del post recién creado (con info del autor)
             $newForo = Foro::findWithAuthor($newForoId); // Obtener datos para la respuesta
             $this->sendJsonResponse($newForo, 201); // 201 Created
         } else {
             // Error al insertar en la base de datos
             $this->sendErrorResponse("Error al crear el post de foro en la base de datos.", 500);
         }
    }

    // PUT /api/v1/foros/{id}
    // Requiere cuerpo JSON con 'titulo' y/o 'contenido'
    public function update($id) {
         // Validar ID del foro
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de foro inválido para actualizar.", 400);
            return;
        }

        $data = $this->getJsonRequestBody();

         if ($data === null) {
            return; // getJsonRequestBody maneja error
        }

        // Validar que existan campos válidos para actualizar en el JSON
        $updateData = [];
        if (isset($data->titulo) && trim($data->titulo) !== '') {
             $updateData['titulo'] = trim($data->titulo);
        }
         if (isset($data->contenido) && trim($data->contenido) !== '') {
             $updateData['contenido'] = trim($data->contenido);
        }

         if (empty($updateData)) {
             $this->sendErrorResponse("Se requieren campos válidos para actualizar (titulo, contenido).", 400);
             return;
         }

        // Verificar si el post de foro existe antes de intentar actualizar
        if (!Foro::find($id)) {
             $this->sendErrorResponse("Post de foro no encontrado para actualizar.", 404);
             return;
        }

        // No permitimos actualizar autor_id a través de este endpoint PUT por ID
        // Si autor_id necesita cambiar, suele ser una acción diferente o con permisos de admin.


        // Usar el método del modelo para actualizar el post de foro
        $success = Foro::update($id, $updateData);

        if ($success) {
            // Si fue exitoso, obtener los datos del post actualizado (con info del autor)
            $updatedForo = Foro::findWithAuthor($id); // Obtener datos para la respuesta
            $this->sendJsonResponse($updatedForo, 200);
        } else {
            // Error al actualizar en la base de datos
            $this->sendErrorResponse("Error al actualizar el post de foro en la base de datos.", 500);
        }
    }

    // DELETE /api/v1/foros/{id}
    public function destroy($id) {
         // Validar ID del foro
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de foro inválido para eliminar.", 400);
            return;
        }

        // Verificar si el post de foro existe
        $foroToDelete = Foro::find($id);
        if (!$foroToDelete) {
             $this->sendErrorResponse("Post de foro no encontrado para eliminar.", 404);
             return;
        }

        // --- IMPORTANTE: Manejar los comentarios dependientes de este post ---
        // Un post de foro no puede ser eliminado si tiene comentarios que lo referencian
        // (a través de comentarios_foro.foro_id -> foros.id) a menos que la FK tenga ON DELETE CASCADE.
        // Si tu FK de foro_id en comentarios_foro *NO* tiene ON DELETE CASCADE,
        // DEBES eliminar los comentarios asociados a este post PRIMERO.
        // Asumiendo que la FK *NO* tiene ON DELETE CASCADE:
        try {
            $connection = ControllerDatabase::connect(); // Obtener conexión DB

            // Eliminar comentarios que pertenecen a este post de foro
            $queryComments = "DELETE FROM comentarios_foro WHERE foro_id = ?";
            $stmtComments = $connection->prepare($queryComments);
            $stmtComments->execute([$id]); // Ejecutar eliminación de comentarios para este foro

            // Si hubo un error de DB al eliminar comentarios, la PDOException lo capturará.
            // Si no hay comentarios, esto simplemente afectará 0 filas, lo cual es normal.

            // Ahora eliminar el post de foro
            $success = Foro::delete($id); // Llamar al método DELETE del modelo Foro

             if ($success) {
                // Éxito
                $this->sendJsonResponse(["message" => "Post de foro eliminado con éxito, incluyendo sus comentarios asociados (si los tenía)."], 200);
            } else {
                // Error al eliminar el post de foro en sí
                 $this->sendErrorResponse("Error al eliminar el post de foro en la base de datos.", 500);
            }

        } catch (PDOException $e) {
            // Loggear y retornar error de DB
            error_log("Database error during foro or comment deletion (Foro ID: {$id}): " . $e->getMessage());
            $this->sendErrorResponse("Error de base de datos al intentar eliminar el post de foro y sus comentarios.", 500);

        } catch (Exception $e) {
             // Loggear y retornar error genérico
            error_log("Unexpected error during foro deletion (Foro ID: {$id}): " . $e->getMessage());
             $this->sendErrorResponse("Ocurrió un error inesperado al eliminar el post de foro.", 500);
        }

        // Si tu FK comentarios_foro.foro_id *SÍ* tiene ON DELETE CASCADE,
        // no necesitas el código explícito para eliminar comentarios.
        // Solo la llamada a Foro::delete($id) dentro del try/catch sería suficiente.
        // Example if CASCADE is used:
        // try {
        //     $success = Foro::delete($id);
        //     if ($success) { $this->sendJsonResponse(["message" => "Post de foro eliminado con éxito."], 200); } else { $this->sendErrorResponse("Error al eliminar el post de foro en la base de datos.", 500); }
        // } catch (PDOException $e) { ... } catch (Exception $e) { ... }
        // Asegúrate de que tu esquema de base de datos soporta CASCADE si remueves la eliminación explícita de comentarios.
    }

    // TODO: Implementar lógica de autenticación y autorización (quién puede crear/actualizar/eliminar)
}