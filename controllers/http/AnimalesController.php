<?php
// controllers/http/AnimalesController.php

// Rutas relativas desde controllers/http/
// Sube dos niveles (../..) a la raíz, luego baja a models/
require_once __DIR__ . '/../../models/Animales.php'; // Para usar el modelo Animales
// Sube un nivel (../) a controllers/ para el controlador de base de datos
require_once __DIR__ . '/../ControllerDatabase.php'; // Incluir por buena práctica, aunque el modelo lo usa

class AnimalesController {

     // --- Métodos de ayuda (copiados o de un controlador base) ---
    // Si ya tienes un archivo de helpers o un controlador base con estos métodos, úsalos.
    // De lo contrario, copia estas funciones aquí:

     /**
     * Obtiene el cuerpo de la solicitud JSON.
     * Decodifica a un array asociativo.
     * @return array|null Array PHP si es JSON válido, null en caso contrario.
     */
    private function getJsonRequestBody() {
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

    // --- Métodos del Controlador API para Animales ---

    // GET /api/v1/animales
    // Lista todos los animales
    public function index() {
        // Obtener todos los animales como arrays asociativos
        $animales = Animales::findAll();

        // Siempre retornar un array, incluso si está vacío
        $this->sendJsonResponse($animales);
    }

    // GET /api/v1/animales/{id}
    // Obtiene un animal por su ID
    public function show($id) {
         // Validar ID del animal
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de animal inválido.", 400);
            return;
        }

        // Usar el método del modelo para encontrar el animal por ID
        $animal = Animales::find($id);

        if ($animal) {
            $this->sendJsonResponse($animal); // Enviar los datos del animal
        } else {
            $this->sendErrorResponse("Animal no encontrado.", 404); // Animal no encontrado
        }
    }


    // POST /api/v1/animales
    // Crea un nuevo animal
    // Requiere cuerpo JSON con las propiedades del animal
    public function store() {
         // Obtener datos del cuerpo JSON (como array asociativo)
         $data = $this->getJsonRequestBody();

         if ($data === null) {
             return; // getJsonRequestBody ya maneja error de JSON inválido
         }

         // Validar campos requeridos para crear un animal
         // AJUSTA esta lista según los campos que sean MANDATORIOS en tu tabla 'animales'
         $requiredFields = ['nombre', 'nombre_cientifico', 'clase', 'continente', 'habitat', 'dieta', 'informacion', 'imagen']; // Ejemplo de campos requeridos
         // Algunos campos como peso, tamaño, sabias, fecha_nacimiento podrían ser opcionales o tener valores por defecto

         foreach ($requiredFields as $field) {
             if (!isset($data[$field]) || trim($data[$field]) === '') {
                  $this->sendErrorResponse("El campo '" . $field . "' es requerido y no puede estar vacío.", 400);
                  return;
             }
             // Recortar espacios en blanco para campos de texto
             if (is_string($data[$field])) {
                  $data[$field] = trim($data[$field]);
             }
         }

         // Validaciones adicionales para tipos de datos específicos si se proporcionan
         // Ejemplo de validación para 'peso' y 'tamano' si deben ser numéricos y se proporcionan
         if (isset($data['peso']) && !is_numeric($data['peso'])) {
              $this->sendErrorResponse("El campo 'peso' debe ser numérico si se proporciona.", 400);
              return;
         }
          if (isset($data['tamano']) && !is_numeric($data['tamano'])) {
              $this->sendErrorResponse("El campo 'tamano' debe ser numérico si se proporciona.", 400);
              return;
         }
         

         // Pasar los datos validados (y recortados) al método create del modelo
         // El método create del modelo se encarga de insertar solo los campos presentes en $data
         $newAnimalId = Animales::create($data);

         if ($newAnimalId !== false) {
             // Si fue exitoso, obtener los datos completos del animal recién creado para la respuesta
             $newAnimal = Animales::find($newAnimalId); // Obtener datos para la respuesta
             $this->sendJsonResponse($newAnimal, 201); // 201 Created
         } else {
             // Error al insertar en la base de datos (el modelo ya loggea el error específico de DB)
             $this->sendErrorResponse("Error al crear el animal en la base de datos.", 500);
         }
    }

    // PUT /api/v1/animales/{id}
    // Actualiza un animal existente
    // Requiere cuerpo JSON con los campos a actualizar
    public function update($id) {
         // Validar ID del animal
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de animal inválido para actualizar.", 400);
            return;
        }

        // Obtener datos del cuerpo JSON (como array asociativo)
        $data = $this->getJsonRequestBody();

         if ($data === null) {
            return; // getJsonRequestBody maneja error
        }

        // Validar que existan *algunos* campos para actualizar en el JSON
         if (empty($data)) {
             $this->sendErrorResponse("Se requieren campos para actualizar.", 400);
             return;
         }

        // Validar campos específicos si se proporcionan (similar a store)
        // Puedes copiar las validaciones de tipo de 'store' aquí para los campos relevantes
        // Ejemplo:
         if (isset($data['peso']) && !is_numeric($data['peso'])) {
              $this->sendErrorResponse("El campo 'peso' debe ser numérico si se proporciona.", 400);
              return;
         }
          if (isset($data['tamano']) && !is_numeric($data['tamano'])) {
              $this->sendErrorResponse("El campo 'tamano' debe ser numérico si se proporciona.", 400);
              return;
         }
         

        // Verificar si el animal existe antes de intentar actualizar
        if (!Animales::find($id)) {
             $this->sendErrorResponse("Animal no encontrado para actualizar.", 404);
             return;
        }

        // Pasar los datos al método update del modelo (permite actualización parcial)
        // El modelo se encarga de filtrar campos no permitidos y construir la consulta UPDATE.
        $success = Animales::update($id, $data);

        if ($success) {
            // Si fue exitoso, obtener los datos actualizados del animal para la respuesta
            $updatedAnimal = Animales::find($id); // Obtener datos para la respuesta
            $this->sendJsonResponse($updatedAnimal, 200); // 200 OK
        } else {
             // Error durante la actualización (el modelo ya loggea el error específico de DB)
             // Esto también puede retornar false si $data estaba vacío (ya validado) o no contenía campos permitidos.
             // Dado que ya validamos que $data no está vacío, un false aquí probablemente es un error de DB.
            $this->sendErrorResponse("Error al actualizar el animal en la base de datos.", 500);
        }
    }

    // DELETE /api/v1/animales/{id}
    // Elimina un animal
    public function destroy($id) {
         // Validar ID del animal
        if (!filter_var($id, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)))) {
            $this->sendErrorResponse("ID de animal inválido para eliminar.", 400);
            return;
        }

        // Verificar si el animal existe antes de intentar eliminar
        $animalToDelete = Animales::find($id);
        if (!$animalToDelete) {
             $this->sendErrorResponse("Animal no encontrado para eliminar.", 404);
             return;
        }

        // --- IMPORTANTE: Manejar restricciones de clave foránea ---
        // Si otras tablas (ej: ubicaciones_animal, registro_medico) tienen claves foráneas
        // que *referencian* animales.id, DEBES eliminar o reasignar esas filas PRIMERO,
        // o definir las FKs con ON DELETE CASCADE en tu esquema de base de datos.
        // Si no haces esto y existen esas dependencias, obtendrás un error 500
        // (violación de restricción de integridad).
        // Asumiendo por ahora que NO hay tablas que referencien animales.id.

        // Usar el método del modelo para eliminar el animal
        $success = Animales::delete($id); // El método del modelo ya loggea errores de DB

        if ($success) {
            // Éxito: 200 OK con mensaje o 204 No Content
            $this->sendJsonResponse(["message" => "Animal eliminado con éxito."], 200);
        } else {
             // Error durante la eliminación (posiblemente por restricción de clave foránea u otro error de DB)
             // El modelo ya loggeó el error específico.
            $this->sendErrorResponse("Error al eliminar el animal en la base de datos. Puede haber datos relacionados que impiden la eliminación.", 500); // Mensaje más informativo
        }
    }

    // TODO: Implementar lógica de autenticación y autorización (ej: solo admin puede CRUD animales)
    // TODO: Implementar la subida y gestión de archivos de imagen de forma segura en los métodos store/update
}