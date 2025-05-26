<?php

require_once __DIR__ . '/../../models/ComentarioForo.php';
require_once __DIR__ . '/../../models/Usuarios.php'; 
require_once __DIR__ . '/../ControllerDatabase.php';

class ComentarioForoController {

   
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

  
    private function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data);
    }

  
    private function sendErrorResponse($message, $statusCode = 500) {
        http_response_code($statusCode);
        echo json_encode(["message" => $message]);
    }


    // GET /api/v1/comentarios/{id}
    public function show($id) {
         // Validar ID del comentario
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de comentario inválido.", 400);
            return;
        }

        $comment = ComentarioForo::findWithAuthor($id); // O ComentarioForo::find($id) si no necesitas datos del autor

        if ($comment) {
            $this->sendJsonResponse($comment);
        } else {
            $this->sendErrorResponse("Comentario no encontrado.", 404);
        }
    }

     // GET /api/v1/foros/{foro_id}/comentarios
     public function indexByForo($foroId) {
         // Validar ID del foro
        if (!filter_var($foroId, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de foro inválido.", 400);
            return;
        }

        // Usar el método del modelo para obtener comentarios de este foro, incluyendo datos del autor
        $comments = ComentarioForo::findByForoIdWithAuthor($foroId); // O ComentarioForo::findByForoId($foroId)

        $this->sendJsonResponse($comments);
     }


    // POST /api/v1/foros/{foro_id}/comentarios
    // Requiere cuerpo JSON con 'autor_id' y 'contenido'
    public function store($foroId) {
         // Validar ID del foro desde la URL
         if (!filter_var($foroId, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
             $this->sendErrorResponse("ID de foro inválido para crear comentario.", 400);
             return;
         }

         $data = $this->getJsonRequestBody();

         if ($data === null) {
             return; // getJsonRequestBody maneja error de JSON inválido
         }

         // Validar campos requeridos en el cuerpo JSON
         $requiredFields = ['autor_id', 'contenido'];
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
              $this->sendErrorResponse("El autor con ID " . $autorId . " no existe.", 404); // O 400 si prefieres
              return;
         }

         $commentData = [
             'foro_id' => $foroId, // Viene de la URL (validado)
             'autor_id' => $autorId, // Viene del cuerpo JSON (validado)
             'contenido' => trim($data->contenido)
         ];

         // Usar el método del modelo para crear el comentario
         $newCommentId = ComentarioForo::create($commentData);

         if ($newCommentId !== false) {
             // Si fue exitoso, obtener los datos del comentario recién creado (con info del autor)
             $newComment = ComentarioForo::findWithAuthor($newCommentId); // Obtener datos para la respuesta
             $this->sendJsonResponse($newComment, 201); // 201 Created
         } else {
             // Error al insertar en la base de datos
             $this->sendErrorResponse("Error al crear el comentario en la base de datos.", 500);
         }
    }

    // PUT /api/v1/comentarios/{id}
    // Requiere cuerpo JSON con 'contenido'
    public function update($id) {
         // Validar ID del comentario
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de comentario inválido para actualizar.", 400);
            return;
        }

        $data = $this->getJsonRequestBody();

         if ($data === null) {
            return; // getJsonRequestBody maneja error
        }

        // Validar campo requerido en el cuerpo JSON (solo 'contenido' permitido para actualizar por defecto)
        if (!isset($data->contenido) || trim($data->contenido) === '') {
             $this->sendErrorResponse("El campo 'contenido' es requerido y no puede estar vacío para actualizar.", 400);
             return;
        }

        // Verificar si el comentario existe antes de intentar actualizar
        if (!ComentarioForo::find($id)) {
             $this->sendErrorResponse("Comentario no encontrado para actualizar.", 404);
             return;
        }

        // Preparar datos para el método update del modelo
        $updateData = [
            'contenido' => trim($data->contenido)
        ];

        // Usar el método del modelo para actualizar el comentario
        $success = ComentarioForo::update($id, $updateData);

        if ($success) {
            // Si fue exitoso, obtener los datos del comentario actualizado (con info del autor)
            $updatedComment = ComentarioForo::findWithAuthor($id); // Obtener datos para la respuesta
            $this->sendJsonResponse($updatedComment, 200);
        } else {
            // Error al actualizar en la base de datos (aunque update del modelo devuelve false si no hay campos válidos, ya lo validamos arriba)
            $this->sendErrorResponse("Error al actualizar el comentario en la base de datos.", 500);
        }
    }

    // DELETE /api/v1/comentarios/{id}
    public function destroy($id) {
         // Validar ID del comentario
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de comentario inválido para eliminar.", 400);
            return;
        }

        // Verificar si el comentario existe antes de intentar eliminar
        if (!ComentarioForo::find($id)) {
             $this->sendErrorResponse("Comentario no encontrado para eliminar.", 404);
             return;
        }

        // Usar el método del modelo para eliminar el comentario
        $success = ComentarioForo::delete($id);

        if ($success) {
            // Éxito: 200 OK con mensaje o 204 No Content (si no se devuelve cuerpo)
            $this->sendJsonResponse(["message" => "Comentario eliminado con éxito."], 200);
        } else {
            // Error al eliminar en la base de datos
            $this->sendErrorResponse("Error al eliminar el comentario en la base de datos.", 500);
        }
    }

}