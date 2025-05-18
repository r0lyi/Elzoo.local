<?php
// controllers/http/NoticiasController.php
// Este controlador maneja las operaciones API para las noticias.
// Se ha creado para operar sin el campo autor_id en el modelo Noticias.

// Rutas relativas desde controllers/http/
require_once __DIR__ . '/../../models/Noticias.php'; // Para usar el modelo Noticias
// No se necesita incluir el modelo Usuarios aquí si no se usa para validar o obtener datos del autor en este controlador específico.
// require_once __DIR__ . '/../../models/Usuarios.php';
// Sube un nivel (../) a controllers/ para el controlador de base de datos
require_once __DIR__ . '/../ControllerDatabase.php'; // Incluir por buena práctica, aunque el modelo lo usa

class NoticiasController {

     // --- Métodos de ayuda (copiados o de un controlador base) ---
    // Si ya tienes un archivo de helpers o un controlador base con estos métodos, úsalos.
    // De lo contrario, copia estas funciones aquí:

     /**
     * Gets the JSON request body and decodes it into an associative array.
     * Sends a 400 error response if the JSON is invalid.
     * @return array|null Associative array on success, null on failure (error response sent).
     */
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

    /**
     * Sends a JSON response with a specified HTTP status code.
     * @param mixed $data The data to encode as JSON.
     * @param int $statusCode The HTTP status code (default 200).
     */
    private function sendJsonResponse(mixed $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        echo json_encode($data);
    }

     /**
     * Sends a JSON error response with a specified HTTP status code.
     * Logs the error internally.
     * @param string $message The error message to send to the client.
     * @param int $statusCode The HTTP status code (default 500).
     */
    private function sendErrorResponse(string $message, int $statusCode = 500): void {
        // Opcionalmente loggear el error específico internamente si es necesario, aunque los métodos del modelo ya loggean errores de DB
        // error_log("API Error ({$statusCode}): " . $message);
        http_response_code($statusCode);
        echo json_encode(["message" => $message]);
    }

    // --- Métodos del Controlador API para Noticias ---

    // GET /api/v1/noticias
    // Lista todas las noticias
    public function index(): void {
        // Obtener todas las noticias como arrays asociativos
        $noticias = Noticias::findAll(); // Usar findAll ya que los métodos WithAuthor han sido eliminados

        // Siempre retornar un array, incluso si está vacío
        $this->sendJsonResponse($noticias);
    }

    // GET /api/v1/noticias/{id}
    // Obtiene una noticia por su ID
    public function show(int $id): void {
         // Validar ID de la noticia desde la URL
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de noticia inválido.", 400);
            return;
        }

        // Usar el método del modelo para encontrar la noticia por ID
        $noticia = Noticias::find($id); // Usar find ya que los métodos WithAuthor han sido eliminados

        if ($noticia) {
            $this->sendJsonResponse($noticia); // Enviar los datos de la noticia
        } else {
            $this->sendErrorResponse("Noticia no encontrada.", 404); // Noticia no encontrada
        }
    }


    // POST /api/v1/noticias
    // Crea una nueva noticia
    // Requiere cuerpo JSON con las propiedades de la noticia (sin autor_id)
    public function store(): void {
         // Obtener datos del cuerpo JSON (como array asociativo)
         $data = $this->getJsonRequestBody();

         if ($data === null) {
             return; // getJsonRequestBody ya maneja error de JSON inválido y envía respuesta
         }

         // Validar campos requeridos para crear una noticia (lista actualizada sin autor_id)
         // Basado en la firma actualizada del método create del modelo, estos campos son esperados.
         // Ajusta esta lista según los campos que sean MANDATORIOS en tu tabla 'noticias' si es necesario.
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

         // Validaciones adicionales para tipos de datos específicos
         // Validar formato de fecha_publicacion
         if (isset($data['fecha_publicacion']) && !strtotime($data['fecha_publicacion'])) {
              $this->sendErrorResponse("El campo 'fecha_publicacion' debe ser una fecha/hora válida si se proporciona.", 400);
              return;
         }

         // No se necesita validación ni manejo del autor_id aquí.

         // TODO: Implementar la lógica segura para manejar la subida de archivos de imagen aquí si 'imagen' es un archivo.
         // Si 'imagen' es solo una ruta/URL, asegúrate de que sea válida si es necesario.


         // Pasar los datos validados (y recortados) al método create del modelo
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
    // Actualiza una noticia existente (permite actualización parcial)
    // Requiere cuerpo JSON con los campos a actualizar (sin autor_id)
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

        // Validar que existan *algunos* campos para actualizar en el JSON
         if (empty($data)) {
             $this->sendErrorResponse("Se requieren campos para actualizar.", 400);
             return;
         }

        // Validar campos específicos si se proporcionan en el JSON
        // No se necesita validación ni manejo del autor_id aquí.

         // Validar formato de fecha_publicacion si se presenta en el JSON
         if (isset($data['fecha_publicacion']) && !strtotime($data['fecha_publicacion'])) {
              $this->sendErrorResponse("El campo 'fecha_publicacion' debe ser una fecha/hora válida si se proporciona.", 400);
              return;
         }

         // Recortar espacios en blanco para campos de texto si se proporcionan
         if (isset($data['titulo']) && is_string($data['titulo'])) $data['titulo'] = trim($data['titulo']);
         if (isset($data['descripcion']) && is_string($data['descripcion'])) $data['descripcion'] = trim($data['descripcion']);
         if (isset($data['url_origen']) && is_string($data['url_origen'])) $data['url_origen'] = trim($data['url_origen']);
         if (isset($data['imagen']) && is_string($data['imagen'])) $data['imagen'] = trim($data['imagen']);

         // TODO: Implementar la lógica segura para manejar la subida de archivos de imagen aquí si 'imagen' es un archivo.


         // Check if the news exists before attempting update
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
             // Error durante la actualización (el modelo ya loggea el error específico de DB)
             // El método update del modelo retorna false si no se proporcionaron campos válidos (ya validado por empty($data)).
             // Dado que ya validamos que $data no está vacío, un false aquí probablemente es un error de DB.
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

        // --- IMPORTANT: Handle foreign key constraints if other tables reference noticias.id ---
        // Si tienes tablas (ej: 'comentarios_noticia', 'likes_noticia', etc.)
        // que tienen claves foráneas referenciando 'noticias.id', DEBES eliminar o reasignar
        // esas filas PRIMERO en el controlador (antes de llamar a Noticias::delete),
        // o definir ON DELETE CASCADE en esas claves foráneas en tu esquema de base de datos.
        // Si existen dependencias y no se manejan, Noticias::delete fallará,
        // loggear una PDOException (Integrity constraint violation), retornará false, y este método
        // enviará una respuesta genérica 500.
        // Asumiendo por ahora que NO hay tablas con FKs que referencien directamente a 'noticias'.
        // Si las hubiera, añadirías DELETE queries aquí similar a como hicimos para usuarios/foros/comments.
        /*
        try {
            $connection = ControllerDatabase::connect(); // Obtener conexión DB

            // Ejemplo: Eliminar comentarios relacionados con esta noticia si existen y están vinculados por noticia_id
            // $queryRelatedComments = "DELETE FROM noticia_comentarios WHERE noticia_id = ?";
            // $stmtRelatedComments = $connection->prepare($queryRelatedComments);
            // $stmtRelatedComments->execute([$id]);

            // Ejemplo: Eliminar likes relacionados con esta noticia si existen y están vinculados por noticia_id
            // $queryRelatedLikes = "DELETE FROM noticia_likes WHERE noticia_id = ?";
            // $stmtRelatedLikes = $connection->prepare($queryRelatedLikes);
            // $stmtRelatedLikes->execute([$id]);

            // Ahora eliminar la noticia en sí
            $success = Noticias::delete($id); // El método del modelo incluye logging básico de errores de DB

            if ($success) {
                 // Respuesta de éxito
                 $this->sendJsonResponse(["message" => "Noticia eliminada con éxito, incluyendo datos asociados."], 200);
            } else {
                 // Error durante la eliminación (probablemente error de DB o FK)
                 $this->sendErrorResponse("Error al eliminar la noticia en la base de datos.", 500);
            }

        } catch (PDOException $e) {
             // Loggear y retornar error de DB
            error_log("Database error durante la eliminación de noticia o datos relacionados (ID de Noticia: {$id}): " . $e->getMessage());
            $this->sendErrorResponse("Error de base de datos al intentar eliminar la noticia y sus datos relacionados.", 500);

        } catch (Exception $e) {
             // Loggear y retornar error genérico
            error_log("Unexpected error durante la eliminación de noticia (ID de Noticia: {$id}): " . $e->getMessage());
             $this->sendErrorResponse("Ocurrió un error inesperado al eliminar la noticia.", 500);
        }
        // Retornar aquí si se usa el bloque try/catch de arriba
        return; // Salir del método después de manejar la eliminación
        */


        // Si NO estás manejando datos relacionados en el controlador y asumes que NO hay FKs o están configuradas con CASCADE:
        $success = Noticias::delete($id); // El método del modelo incluye logging básico de errores de DB

        if ($success) {
            // En éxito: 200 OK con mensaje de éxito o 204 No Content (si no se desea cuerpo en la respuesta)
            $this->sendJsonResponse(["message" => "Noticia eliminada con éxito."], 200);
        } else {
             // Error durante la eliminación (el modelo loggea el error específico de DB)
             // Si hay una violación de restricción de clave foránea, model::delete retorna false y lo loggea.
             // El usuario recibe esta respuesta genérica 500 aquí.
            $this->sendErrorResponse("Error al eliminar la noticia en la base de datos. Puede haber datos relacionados que impiden la eliminación.", 500); // Mensaje más informativo
        }
    }

    // TODO: Implementar lógica de autenticación y autorización (ej: solo admin o usuarios específicos pueden CRUD noticias)
    // TODO: Implementar la gestión segura de subida de archivos de imagen en los métodos store/update (este controlador solo espera path/URL en JSON)
}