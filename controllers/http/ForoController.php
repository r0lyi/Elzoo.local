<?php
// controllers/http/ForoController.php

require_once __DIR__ . '/../../models/Foro.php';
require_once __DIR__ . '/../../models/Usuarios.php';
// ATENCIÓN: Revisa este require_once. Si tu archivo se llama 'ComentarioForo.php' (singular),
// entonces deberás cambiarlo a '/ComentarioForo.php'.
require_once __DIR__ . '/../../models/ComentarioForo.php';
require_once __DIR__ . '/../ControllerDatabase.php';

class ForoController {

    private function getJsonRequestBody() {
        $input = file_get_contents('php://input');
        $data = json_decode($input);
        if (json_last_error() !== JSON_ERROR_NONE) {
             http_response_code(400);
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

    // GET /api/v1/foros (Ahora maneja los filtros también)
    public function index() {
        $criteria = [];

        // Recopilar los parámetros de filtro de la query string
        if (isset($_GET['titulo']) && trim($_GET['titulo']) !== '') {
            $criteria['titulo'] = trim($_GET['titulo']);
        }
        // ¡NUEVO! Recopilar el filtro por nombre de autor
        if (isset($_GET['autor_nombre']) && trim($_GET['autor_nombre']) !== '') {
            $criteria['autor_nombre'] = trim($_GET['autor_nombre']);
        }
        // Si no se pasaron criterios, $criteria estará vacío, y filterWithAuthor devolverá todos.
        $foros = Foro::filterWithAuthor($criteria);
        $this->sendJsonResponse($foros);
    }

    // GET /api/v1/foros/{id}
    public function show($id) {
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de foro inválido.", 400);
            return;
        }
        $foro = Foro::findWithAuthor($id);
        if ($foro) {
            $this->sendJsonResponse($foro);
        } else {
            $this->sendErrorResponse("Post de foro no encontrado.", 404);
        }
    }

    // POST /api/v1/foros
    public function store() {
         $data = $this->getJsonRequestBody();
         $dataArray = json_decode(json_encode($data), true); // Convertir a array

         if ($dataArray === null) {
             return;
         }
         $requiredFields = ['titulo', 'contenido', 'autor_id'];
         foreach ($requiredFields as $field) {
             if (!isset($dataArray[$field]) || trim((string)$dataArray[$field]) === '') {
                  $this->sendErrorResponse("El campo '" . $field . "' es requerido y no puede estar vacío.", 400);
                  return;
             }
         }
         $autorId = (int) $dataArray['autor_id'];
         if (!filter_var($autorId, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
              $this->sendErrorResponse("ID de autor inválido.", 400);
              return;
         }
         if (!Usuarios::find($autorId)) {
              $this->sendErrorResponse("El autor con ID " . $autorId . " no existe.", 404);
              return;
         }
         $foroData = [
             'titulo' => trim($dataArray['titulo']),
             'contenido' => trim($dataArray['contenido']),
             'autor_id' => $autorId
         ];
         $newForoId = Foro::create($foroData);
         if ($newForoId !== false) {
             $newForo = Foro::findWithAuthor($newForoId);
             $this->sendJsonResponse($newForo, 201);
         } else {
             $this->sendErrorResponse("Error al crear el post de foro en la base de datos.", 500);
         }
    }

    // PUT /api/v1/foros/{id}
    public function update($id) {
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de foro inválido para actualizar.", 400);
            return;
        }
        $data = $this->getJsonRequestBody();
        $dataArray = json_decode(json_encode($data), true); // Convertir a array

        if ($dataArray === null) {
            return;
        }
        $updateData = [];
        if (isset($dataArray['titulo']) && trim((string)$dataArray['titulo']) !== '') {
             $updateData['titulo'] = trim((string)$dataArray['titulo']);
        }
        if (isset($dataArray['contenido']) && trim((string)$dataArray['contenido']) !== '') {
             $updateData['contenido'] = trim((string)$dataArray['contenido']);
        }
        if (empty($updateData)) {
             $this->sendErrorResponse("Se requieren campos válidos para actualizar (titulo, contenido).", 400);
             return;
        }
        if (!Foro::find($id)) {
             $this->sendErrorResponse("Post de foro no encontrado para actualizar.", 404);
             return;
        }
        $success = Foro::update($id, $updateData);
        if ($success) {
            $updatedForo = Foro::findWithAuthor($id);
            $this->sendJsonResponse($updatedForo, 200);
        } else {
            $this->sendErrorResponse("Error al actualizar el post de foro en la base de datos.", 500);
        }
    }

    // DELETE /api/v1/foros/{id}
    public function destroy($id) {
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de foro inválido para eliminar.", 400);
            return;
        }
        $foroToDelete = Foro::find($id);
        if (!$foroToDelete) {
             $this->sendErrorResponse("Post de foro no encontrado para eliminar.", 404);
             return;
        }
        try {
            // ATENCIÓN: Cambiado a singular, asumiendo que tu modelo de comentarios es 'ComentarioForo'
            ComentarioForo::deleteByForoId($id); // Eliminar comentarios asociados

            $success = Foro::delete($id);
            if ($success) {
                $this->sendJsonResponse(["message" => "Post de foro eliminado con éxito, incluyendo sus comentarios asociados (si los tenía)."], 200);
            } else {
                $this->sendErrorResponse("Error al eliminar el post de foro en la base de datos.", 500);
            }
        } catch (PDOException $e) {
            error_log("Database error during foro or comment deletion (Foro ID: {$id}): " . $e->getMessage());
            $this->sendErrorResponse("Error de base de datos al intentar eliminar el post de foro y sus comentarios.", 500);
        } catch (Exception $e) {
            error_log("Unexpected error during foro deletion (Foro ID: {$id}): " . $e->getMessage());
             $this->sendErrorResponse("Ocurrió un error inesperado al eliminar el post de foro.", 500);
        }
    }
}