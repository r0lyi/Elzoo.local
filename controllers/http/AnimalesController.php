<?php
// controllers/http/AnimalesController.php

require_once __DIR__ . '/../../models/Animales.php';
require_once __DIR__ . '/../ControllerDatabase.php';

class AnimalesController {

    private function getJsonRequestBody() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
             http_response_code(400);
             echo json_encode(["message" => "Solicitud JSON inválida. Error: " . json_last_error_msg()]);
             return null;
        }
        return $data;
    }

    private function sendJsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }

    private function sendErrorResponse($message, $statusCode = 500) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode(["message" => $message]);
    }

    // --- Métodos del Controlador API para Animales ---

    public function index() {
        $animales = Animales::findAll();
        $this->sendJsonResponse($animales);
    }

    public function show($id) {
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de animal inválido.", 400);
            return;
        }
        $animal = Animales::find($id);
        if ($animal) {
            $this->sendJsonResponse($animal);
        } else {
            $this->sendErrorResponse("Animal no encontrado.", 404);
        }
    }

    public function store() {
        $data = $this->getJsonRequestBody();
        if ($data === null) {
            return;
        }

        // Definir todos los campos requeridos para la creación (fecha_registro se gestiona en el modelo/DB)
        $requiredFields = ['nombre', 'nombre_cientifico', 'clase', 'continente', 'habitat', 'dieta', 'informacion', 'imagen'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                 $this->sendErrorResponse("El campo '" . $field . "' es requerido y no puede estar vacío.", 400);
                 return;
            }
            if (is_string($data[$field])) {
                 $data[$field] = trim($data[$field]);
            }
        }

        // Validaciones de tipo para campos opcionales numéricos
        if (isset($data['peso']) && !is_numeric($data['peso']) && $data['peso'] !== null && $data['peso'] !== '') {
             $this->sendErrorResponse("El campo 'peso' debe ser numérico si se proporciona.", 400);
             return;
        }
        if (isset($data['tamano']) && !is_numeric($data['tamano']) && $data['tamano'] !== null && $data['tamano'] !== '') {
             $this->sendErrorResponse("El campo 'tamano' debe ser numérico si se proporciona.", 400);
             return;
        }
        // fecha_registro ya no se valida aquí, se auto-asigna

        $newAnimalId = Animales::create($data);

        if ($newAnimalId !== false) {
            $newAnimal = Animales::find($newAnimalId);
            $this->sendJsonResponse($newAnimal, 201);
        } else {
            $this->sendErrorResponse("Error al crear el animal en la base de datos.", 500);
        }
    }

    public function update($id) {
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de animal inválido para actualizar.", 400);
            return;
        }
        $data = $this->getJsonRequestBody();
        if ($data === null) {
            return;
        }

        // Remove fecha_registro from data if it was accidentally sent by frontend
        unset($data['fecha_registro']);

        // Validaciones de tipo para campos opcionales numéricos/fecha si se proporcionan
        if (isset($data['peso']) && !is_numeric($data['peso']) && $data['peso'] !== null && $data['peso'] !== '') {
             $this->sendErrorResponse("El campo 'peso' debe ser numérico si se proporciona.", 400);
             return;
        }
        if (isset($data['tamano']) && !is_numeric($data['tamano']) && $data['tamano'] !== null && $data['tamano'] !== '') {
             $this->sendErrorResponse("El campo 'tamano' debe ser numérico si se proporciona.", 400);
             return;
        }
        // fecha_registro no se valida aquí, no se permite actualizar

        if (!Animales::find($id)) {
             $this->sendErrorResponse("Animal no encontrado para actualizar.", 404);
             return;
        }

        $success = Animales::update($id, $data);

        if ($success) {
            $updatedAnimal = Animales::find($id);
            $this->sendJsonResponse($updatedAnimal, 200);
        } else {
            $this->sendErrorResponse("Error al actualizar el animal en la base de datos.", 500);
        }
    }

    public function destroy($id) {
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de animal inválido para eliminar.", 400);
            return;
        }
        $animalToDelete = Animales::find($id);
        if (!$animalToDelete) {
             $this->sendErrorResponse("Animal no encontrado para eliminar.", 404);
             return;
        }
        $success = Animales::delete($id);
        if ($success) {
            $this->sendJsonResponse(["message" => "Animal eliminado con éxito."], 200);
        } else {
            $this->sendErrorResponse("Error al eliminar el animal en la base de datos. Puede haber datos relacionados que impiden la eliminación.", 500);
        }
    }

    public function filter() {
        $filters = $_GET;
        $animalesFiltrados = Animales::filter($filters);
        $this->sendJsonResponse($animalesFiltrados);
    }
}