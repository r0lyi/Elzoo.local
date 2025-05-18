<?php
// models/Animales.php

require_once __DIR__ . '/../controllers/ControllerDatabase.php';

class Animales {
    private $id;
    private $nombre;
    private $nombre_cientifico;
    private $clase;
    private $continente;
    private $habitat;
    private $dieta;
    private $peso; // Asumimos que es numérico
    private $tamano; // Asumimos que es numérico
    private $informacion;
    private $sabias;
    private $imagen; // Asumimos que almacena la ruta o URL de la imagen
    private $fecha_nacimiento; // Asumimos que es un campo de fecha/hora
 

    // Mantener __construct existente
    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    // Mantener Getters existentes
    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function getNombreCientifico() { return $this->nombre_cientifico; }
    public function getClase() { return $this->clase; }
    public function getContinente() { return $this->continente; }
    public function getHabitat() { return $this->habitat; }
    public function getDieta() { return $this->dieta; }
    public function getPeso() { return $this->peso; }
    public function getTamano() { return $this->tamano; }
    public function getInformacion() { return $this->informacion; }
    public function getSabias() { return $this->sabias; }
    public function getImagen() { return $this->imagen; }
    public function getFechaNacimiento() { return $this->fecha_nacimiento; }

    // Mantener Setters existentes
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setNombreCientifico($nombre_cientifico) { $this->nombre_cientifico = $nombre_cientifico; }
    public function setClase($clase) { $this->clase = $clase; }
    public function setContinente($continente) { $this->continente = $continente; }
    public function setHabitat($habitat) { $this->habitat = $habitat; }
    public function setDieta($dieta) { $this->dieta = $dieta; }
    public function setPeso($peso) { $this->peso = $peso; }
    public function setTamano($tamano) { $this->tamano = $tamano; }
    public function setInformacion($informacion) { $this->informacion = $informacion; }
    public function setSabias($sabias) { $this->sabias = $sabias; }
    public function setImagen($imagen) { $this->imagen = $imagen; } // Nota: la subida/gestión de archivos de imagen es compleja y no se maneja solo con este método
    public function setFechaNacimiento($fecha_nacimiento) { $this->fecha_nacimiento = $fecha_nacimiento; }

    // Mantener método 'getAnimales' existente (retorna OBJETOS Animales)
    // Método para obtener todos los animales (Retorna OBJETOS Animales)
    public static function getAnimales() {
        $db = ControllerDatabase::connect();

        if ($db === null) {
            return [];
        }

        $stmt = $db->query("SELECT * FROM animales");
        $animalesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $animales = [];
        foreach ($animalesData as $animalData) {
            $animales[] = new Animales($animalData);
        }

        return $animales;
    }

    // Mantener método 'getPorNombre' existente (encuentra por slug, retorna OBJETO Animales)
    public static function getPorNombre(string $slug): ?Animales {
        $db = ControllerDatabase::connect();
        if ($db === null) {
            return null;
        }
        $sql = "SELECT * FROM animales
                WHERE LOWER(REPLACE(nombre, ' ', '-')) = LOWER(:slug)
                LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return new Animales($data);
        }
        return null;
    }

    // --- Nuevos Métodos estáticos para API CRUD (retornan arrays asociativos o IDs) ---

    // Encontrar un animal por su ID (retorna array asociativo o null)
    // Versión API-friendly
    public static function find($id): ?array {
        $db = ControllerDatabase::connect();
        $stmt = $db->prepare("SELECT * FROM animales WHERE id = ?");
        $stmt->execute([$id]);
        $animal = $stmt->fetch(PDO::FETCH_ASSOC);
        return $animal ?: null; // Retorna null si no se encuentra
    }

     // Encontrar todos los animales (retorna array de arrays asociativos)
     // Versión API-friendly
    public static function findAll(): array {
        $db = ControllerDatabase::connect();
        // Ordenar por ID ascendente por consistencia en la API
        $stmt = $db->query("SELECT * FROM animales ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna arrays asociativos
    }

    // Crear nuevo animal a partir de un array de datos (retorna el ID del nuevo animal o false)
    // Más adecuado para recibir datos JSON en la API
    public static function create(array $data): int|false {
        $db = ControllerDatabase::connect();
        // Define los campos posibles que se pueden insertar
        $allowedFields = ['nombre', 'nombre_cientifico', 'clase', 'continente', 'habitat', 'dieta', 'peso', 'tamano', 'informacion', 'sabias', 'imagen', 'fecha_nacimiento'];

        $insertFields = [];
        $placeholders = [];
        $values = [];

        foreach ($allowedFields as $field) {
            // Solo incluir campos presentes en el array de datos
            // (La validación de campos requeridos se hará en el controlador)
            if (isset($data[$field])) {
                $insertFields[] = "`{$field}`"; // Usar backticks por si hay nombres de columna reservados
                $placeholders[] = ":{$field}"; // Usar placeholders nombrados
                $values[":{$field}"] = $data[$field]; // Almacenar el valor asociado al placeholder
            }
        }

        if (empty($insertFields)) {
            // No se proporcionaron datos válidos para insertar
            return false; // O lanzar una excepción
        }

        // Construir la consulta INSERT
        $sql = "INSERT INTO animales (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $db->prepare($sql);

        try {
             $result = $stmt->execute($values);
             return $result ? $db->lastInsertId() : false; // Retorna el ID del nuevo animal si fue exitoso
        } catch (PDOException $e) {
            // Loggear el error real de la base de datos para depuración
            error_log("Database error in Animales::create: " . $e->getMessage());
            return false; // Indica fallo
        }
    }

    // Actualizar un animal por ID (permite actualización parcial)
    // Recibe un array de datos con los campos a actualizar
    public static function update(int $id, array $data): bool {
         $db = ControllerDatabase::connect();

         // Define todos los campos posibles que se pueden actualizar desde la API
         $allowedFields = ['nombre', 'nombre_cientifico', 'clase', 'continente', 'habitat', 'dieta', 'peso', 'tamano', 'informacion', 'sabias', 'imagen', 'fecha_nacimiento'];

         $updateFields = [];
         $values = [];

         foreach ($allowedFields as $field) {
            // Solo incluir campos presentes en el array de datos Y que están permitidos
            // La validación de formatos específicos (ej: numérico, fecha) se hará en el controlador
            if (isset($data[$field])) {
                $updateFields[] = "`{$field}` = :{$field}"; // Usar placeholders nombrados
                $values[":{$field}"] = $data[$field]; // Almacenar el valor asociado
            }
         }

         if (empty($updateFields)) {
             // No se proporcionaron campos válidos para actualizar
             return false; // O lanzar una excepción
         }

         // Construir la consulta UPDATE
         $sql = "UPDATE animales SET " . implode(', ', $updateFields) . " WHERE id = :id";
         $values[":id"] = $id; // Añadir el ID al array de valores

         $stmt = $db->prepare($sql);

         try {
             return $stmt->execute($values);
         } catch (PDOException $e) {
             error_log("Database error in Animales::update: " . $e->getMessage());
             return false; // Indica fallo
         }
    }

    // Eliminar un animal por ID
    // NOTA: Si otras tablas (ej: ubicaciones_animal, registro_medico) tienen claves foráneas que referencian animales.id,
    // DEBES manejar esas dependencias antes de eliminar al animal (eliminarlas primero o configurar ON DELETE CASCADE en la BD).
    // De lo contrario, obtendrás un error de restricción de integridad.
    public static function delete(int $id): bool {
        $db = ControllerDatabase::connect();
        $sql = "DELETE FROM animales WHERE id = ?";
        $stmt = $db->prepare($sql);
        try {
            // execute devuelve true si la consulta se ejecutó sin errores de DB (incluso si 0 filas afectadas)
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            // Loggear el error de base de datos (importante para depurar si hay problemas de FK)
            error_log("Database error in Animales::delete: " . $e->getMessage());
            // Si el error es una violación de restricción de integridad (SQLSTATE 23000),
            // significa que hay dependencias que impiden la eliminación.
            // Podemos verificar el código de error si queremos un manejo más específico,
            // pero por ahora, simplemente devolvemos false en caso de cualquier error de DB.
            return false; // Indica fallo en la eliminación (posiblemente por FK)
        }
    }

    // Puedes añadir otros métodos útiles aquí si los necesitas (ej: findByHabitat, findByClase)
}