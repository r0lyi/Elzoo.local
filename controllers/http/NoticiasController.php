<?php

require_once __DIR__ . '/../../models/Noticias.php'; 

require_once __DIR__ . '/../ControllerDatabase.php';

class NoticiasController {



   
    private function getJsonRequestBody(): ?array {
        $input = file_get_contents('php://input');
        // Decodificar como array asociativo para pasarlo a los métodos del modelo
        $data = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
             http_response_code(400); // Bad Request
             echo json_encode(["message" => "Solicitud JSON inválida. Error: " . json_last_error_msg()]);
             return null;
        }
        return $data;
    }

    private function sendJsonResponse(mixed $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        echo json_encode($data);
    }

  
    private function sendErrorResponse(string $message, int $statusCode = 500): void {
        
        http_response_code($statusCode);
        echo json_encode(["message" => $message]);
    }

    // --- Métodos del Controlador API para Noticias ---

    // GET /api/v1/noticias
    public function index(): void {
        // Obtener todas las noticias como arrays asociativos
        $noticias = Noticias::findAll(); // Usar findAll ya que los métodos WithAuthor han sido eliminados

        // Siempre retornar un array, incluso si está vacío
        $this->sendJsonResponse($noticias);
    }

    // GET /api/v1/noticias/{id}
    public function show(int $id): void {
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de noticia inválido.", 400);
            return;
        }

       
        $noticia = Noticias::find($id); 

        if ($noticia) {
            $this->sendJsonResponse($noticia); // Enviar los datos de la noticia
        } else {
            $this->sendErrorResponse("Noticia no encontrada.", 404); // Noticia no encontrada
        }
    }


    // POST /api/v1/noticias

    public function store(): void {
         // Obtener datos del cuerpo JSON (como array asociativo)
         $data = $this->getJsonRequestBody();

         if ($data === null) {
             return; // getJsonRequestBody ya maneja error de JSON inválido y envía respuesta
         }

         $requiredFields = ['titulo', 'descripcion', 'fecha_publicacion', 'url_origen', 'imagen'];

         foreach ($requiredFields as $field) {
             // Asegurarse de que el campo existe y no está vacío después de trim
             if (!isset($data[$field]) || trim((string)$data[$field]) === '') { // Convertir a string para trim seguro
                  $this->sendErrorResponse("El campo '" . $field . "' es requerido y no puede estar vacío.", 400);
                  return;
             }
             // Recortar espacios en blanco para campos de texto si son strings
             if (is_string($data[$field])) {
                  $data[$field] = trim($data[$field]);
             }
         }


         if (isset($data['fecha_publicacion']) && !strtotime($data['fecha_publicacion'])) {
              $this->sendErrorResponse("El campo 'fecha_publicacion' debe ser una fecha/hora válida si se proporciona.", 400);
              return;
         }


         // NOTA: El método create del modelo espera que $data contenga todos los campos listados en su firma actualizada (sin autor_id).
         $newNoticiaId = Noticias::create($data);

         if ($newNoticiaId !== false) {
             // Si fue exitoso, obtener los datos completos de la noticia recién creada para la respuesta
             // Obtener los datos de la noticia por ID
             $newNoticia = Noticias::find($newNoticiaId); // Usar find ahora
             $this->sendJsonResponse($newNoticia, 201); // 201 Created
         } else {
             // Error al insertar en la base de datos (el modelo ya loggea el error específico de DB)
             $this->sendErrorResponse("Error al crear la noticia en la base de datos.", 500);
         }
    }

    // PUT /api/v1/noticias/{id}

    public function update(int $id): void {
         // Validar ID de la noticia desde la URL
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de noticia inválido para actualizar.", 400);
            return;
        }

        // Get JSON request body (as associative array)
        $data = $this->getJsonRequestBody();

         if ($data === null) {
            return; // getJsonRequestBody maneja error
        }

         if (empty($data)) {
             $this->sendErrorResponse("Se requieren campos para actualizar.", 400);
             return;
         }

       

         if (isset($data['fecha_publicacion']) && !strtotime($data['fecha_publicacion'])) {
              $this->sendErrorResponse("El campo 'fecha_publicacion' debe ser una fecha/hora válida si se proporciona.", 400);
              return;
         }

         if (isset($data['titulo']) && is_string($data['titulo'])) $data['titulo'] = trim($data['titulo']);
         if (isset($data['descripcion']) && is_string($data['descripcion'])) $data['descripcion'] = trim($data['descripcion']);
         if (isset($data['url_origen']) && is_string($data['url_origen'])) $data['url_origen'] = trim($data['url_origen']);
         if (isset($data['imagen']) && is_string($data['imagen'])) $data['imagen'] = trim($data['imagen']);



        if (!Noticias::find($id)) { // Usar find ya que los métodos WithAuthor han sido eliminados
             $this->sendErrorResponse("Noticia no encontrada para actualizar.", 404);
             return;
        }

        // Pasar los datos al método update del modelo (permite actualización parcial)
        $success = Noticias::update($id, $data); // El método del modelo se encarga de filtrar campos permitidos

        if ($success) {
            // Si fue exitoso, obtener los datos actualizados de la noticia para la respuesta
            $updatedNoticia = Noticias::find($id); // Usar find ya que los métodos WithAuthor han sido eliminados
            $this->sendJsonResponse($updatedNoticia, 200); // 200 OK
        } else {
           
            $this->sendErrorResponse("Error al actualizar la noticia en la base de datos.", 500);
        }
    }

    // DELETE /api/v1/noticias/{id}
    // Elimina una noticia
    public function destroy(int $id): void {
         // Validar ID de la noticia desde la URL
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de noticia inválido para eliminar.", 400);
            return;
        }

        // Check if news exists before attempting delete
        $noticiaToDelete = Noticias::find($id); // Usar find ya que los métodos WithAuthor han sido eliminados
        if (!$noticiaToDelete) {
             $this->sendErrorResponse("Noticia no encontrada para eliminar.", 404);
             return;
        }

       

        $success = Noticias::delete($id); // El método del modelo incluye logging básico de errores de DB

        if ($success) {
            // En éxito: 200 OK con mensaje de éxito o 204 No Content (si no se desea cuerpo en la respuesta)
            $this->sendJsonResponse(["message" => "Noticia eliminada con éxito."], 200);
        } else {
            
            $this->sendErrorResponse("Error al eliminar la noticia en la base de datos. Puede haber datos relacionados que impiden la eliminación.", 500); // Mensaje más informativo
        }
    }

   
}