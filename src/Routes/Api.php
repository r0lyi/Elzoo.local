<?php
namespace Elzoo\Routes;

use Elzoo\Controllers\AnimalesController;

class Api {
    public static function handleApiRequest($method, $uri) {
        // Normalizar la URI (quitar barras iniciales y finales)
        $uri = trim($uri, '/');

        // Instanciar controladores necesarios
        //$animalesController = new AnimalesController();

        // Manejar rutas de la API
        switch (true) {
            // GET /api/animales - Lista todos los animales
        /*    case ($method === 'GET' && $uri === 'api/animales'):
                $animalesController->getAllAnimals();
                break;

            // POST /api/animales - Crear un nuevo animal
            case ($method === 'POST' && $uri === 'api/animales'):
                $animalesController->createAnimal();
                break;

            // GET /api/animales/{id} - Obtener un animal por ID
            case ($method === 'GET' && preg_match('/^api\/animales\/\d+$/', $uri)):
                $id = basename($uri);
                $animalesController->getAnimalById($id);
                break;

            // PUT /api/animales/{id} - Actualizar un animal
            case ($method === 'PUT' && preg_match('/^api\/animales\/\d+$/', $uri)):
                $id = basename($uri);
                $animalesController->updateAnimal($id);
                break;

            // DELETE /api/animales/{id} - Eliminar un animal
            case ($method === 'DELETE' && preg_match('/^api\/animales\/\d+$/', $uri)):
                $id = basename($uri);
                $animalesController->deleteAnimal($id);
                break;                  */

            // Ruta no encontrada
            default:
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode(['message' => 'Ruta no encontrada']);
                break;
        }
    }
}