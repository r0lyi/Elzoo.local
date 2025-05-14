<?php

require_once __DIR__ . '/../controllers/ControllerDatabase.php';

class Animales {
    private $id;
    private $especie;
    private $habitat;
    private $descripcion;

    public function __construct($id = null, $especie = '', $habitat = '', $descripcion = '') {
        $this->id = $id;
        $this->especie = $especie;
        $this->habitat = $habitat;
        $this->descripcion = $descripcion;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getEspecie() {
        return $this->especie;
    }

    public function getHabitat() {
        return $this->habitat;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setEspecie($especie) {
        $this->especie = $especie;
    }

    public function setHabitat($habitat) {
        $this->habitat = $habitat;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    // Método para obtener todos los animales
    public static function getAnimales() {
        $db = ControllerDatabase::connect();
        
        if ($db === null) {
            return [];
        }

        // Consulta para obtener las noticias
        $stmt = $db->query("SELECT * FROM animales");
        $animalesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertir los datos en objetos Noticias
        $animales = [];
        foreach ($animalesData as $animalData) {
            $animal = new Animales(
                $animalData['id'],
                $animalData['especie'],
                $animalData['hábitat'],
                $animalData['descripción'],
            );
            $animales[] = $animal;
        }

        return $animales;
    }
}

?>
