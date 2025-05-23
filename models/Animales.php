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
    private $fecha_registro; // Asumimos que es un campo de fecha/hora
 

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
    public function getFechaRegistro() { return $this->fecha_registro; }

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
    public function setFechaRegistro($fecha_registro) { $this->fecha_registro = $fecha_registro; }

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

     // --- Métodos estáticos para API CRUD (retornan arrays asociativos o IDs) ---

    // Encontrar un animal por su ID (retorna array asociativo o null)
    public static function find($id): ?array {
        $db = ControllerDatabase::connect();
        if ($db === null) {
            error_log("Database connection failed in Animales::find");
            return null;
        }
        $stmt = $db->prepare("SELECT * FROM animales WHERE id = ?");
        $stmt->execute([$id]);
        $animal = $stmt->fetch(PDO::FETCH_ASSOC);
        return $animal ?: null;
    }

    // Encontrar todos los animales (retorna array de arrays asociativos)
    public static function findAll(): array {
        $db = ControllerDatabase::connect();
        if ($db === null) {
            error_log("Database connection failed in Animales::findAll");
            return [];
        }
        $stmt = $db->query("SELECT * FROM animales ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crear nuevo animal a partir de un array de datos (retorna el ID del nuevo animal o false)
    public static function create(array $data): int|false {
        $db = ControllerDatabase::connect();
        if ($db === null) {
            error_log("Database connection failed in Animales::create");
            return false;
        }
        $allowedFields = ['nombre', 'nombre_cientifico', 'clase', 'continente', 'habitat', 'dieta', 'peso', 'tamano', 'informacion', 'sabias_que', 'imagen', 'fecha_registro'];

        $insertFields = [];
        $placeholders = [];
        $values = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $insertFields[] = "`{$field}`";
                $placeholders[] = ":{$field}";
                $values[":{$field}"] = $data[$field];
            }
        }

        if (empty($insertFields)) {
            error_log("No valid fields provided for Animales::create");
            return false;
        }

        $sql = "INSERT INTO animales (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $db->prepare($sql);

        try {
             $result = $stmt->execute($values);
             return $result ? $db->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("Database error in Animales::create: " . $e->getMessage());
            return false;
        }
    }

    // Actualizar un animal por ID (permite actualización parcial de CUALQUIER campo)
    public static function update(int $id, array $data): bool {
         $db = ControllerDatabase::connect();
         if ($db === null) {
            error_log("Database connection failed in Animales::update");
            return false;
         }

         // Define explícitamente todos los campos que pueden ser actualizados
         $allowedFields = ['nombre', 'nombre_cientifico', 'clase', 'continente', 'habitat', 'dieta', 'peso', 'tamano', 'informacion', 'sabias_que', 'imagen', 'fecha_registro'];

         $updateFields = [];
         $values = [];

         foreach ($allowedFields as $field) {
            // Solo incluir campos presentes en el array de datos
            // Y no incluir 'id' en la actualización de campos
            if (array_key_exists($field, $data) && $field !== 'id') { // Usar array_key_exists para permitir nulls o vacíos
                $updateFields[] = "`{$field}` = :{$field}";
                $values[":{$field}"] = $data[$field];
            }
         }

         if (empty($updateFields)) {
             // No se proporcionaron campos válidos para actualizar o solo se intentó actualizar el ID
             return false;
         }

         $sql = "UPDATE animales SET " . implode(', ', $updateFields) . " WHERE id = :id";
         $values[":id"] = $id;

         $stmt = $db->prepare($sql);

         try {
             return $stmt->execute($values);
         } catch (PDOException $e) {
             error_log("Database error in Animales::update: " . $e->getMessage());
             return false;
         }
    }

    // Eliminar un animal por ID
    public static function delete(int $id): bool {
        $db = ControllerDatabase::connect();
        if ($db === null) {
            error_log("Database connection failed in Animales::delete");
            return false;
        }
        $sql = "DELETE FROM animales WHERE id = ?";
        $stmt = $db->prepare($sql);
        try {
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Database error in Animales::delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Filtra animales según los criterios proporcionados.
     * @param array $filters Array asociativo con los filtros (ej. ['clase' => 'Mamífero', 'continente' => 'África']).
     * @return array Array de arrays asociativos de animales que cumplen con los filtros.
     */
    public static function filter(array $filters): array {
        $db = ControllerDatabase::connect();
        if ($db === null) {
            error_log("Database connection failed in Animales::filter");
            return [];
        }

        $allowedFilters = ['id', 'nombre', 'nombre_cientifico', 'clase', 'continente', 'habitat', 'dieta', 'peso', 'tamano', 'fecha_registro'];
        $whereClauses = [];
        $values = [];

        foreach ($filters as $key => $value) {
            if (in_array($key, $allowedFilters) && $value !== null && $value !== '') {
                // Usar LIKE para búsquedas parciales en campos de texto, e.g., nombre
                if (in_array($key, ['nombre', 'nombre_cientifico', 'clase', 'continente', 'habitat', 'dieta'])) {
                    $whereClauses[] = "`{$key}` LIKE :{$key}";
                    $values[":{$key}"] = '%' . $value . '%';
                } elseif (in_array($key, ['peso', 'tamano'])) {
                    // Para números, se espera un valor exacto o rango en el futuro (si se extiende)
                    // Por ahora, búsqueda exacta para numéricos.
                    $whereClauses[] = "`{$key}` = :{$key}";
                    $values[":{$key}"] = $value;
                } elseif ($key === 'fecha_registro') {
                    // Para fechas, se espera un valor exacto.
                    $whereClauses[] = "`{$key}` = :{$key}";
                    $values[":{$key}"] = $value;
                } else {
                    // Para otros campos (como ID), búsqueda exacta
                    $whereClauses[] = "`{$key}` = :{$key}";
                    $values[":{$key}"] = $value;
                }
            }
        }

        $sql = "SELECT * FROM animales";
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }
        $sql .= " ORDER BY id ASC"; // Asegurar orden consistente

        $stmt = $db->prepare($sql);

        try {
            $stmt->execute($values);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in Animales::filter: " . $e->getMessage());
            return [];
        }
    }
}