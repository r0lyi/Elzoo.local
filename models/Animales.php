<?php

require_once __DIR__ . '/../controllers/ControllerDatabase.php';

class Animales {
    private $id;
    private $nombre;
    private $nombre_cientifico;
    private $clase;
    private $continente;
    private $habitat;
    private $dieta;
    private $peso;
    private $tamano;
    private $informacion;
    private $sabias;
    private $imagen;
    private $fecha_nacimiento;
 

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    // Getters
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

    // Setters
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
    public function setImagen($imagen) { $this->imagen = $imagen; }
    public function setFechaNacimiento($fecha_nacimiento) { $this->fecha_nacimiento = $fecha_nacimiento; }

    // Método para obtener todos los animales
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

    // Puedes añadir métodos para guardar, actualizar, eliminar si lo necesitas
}

?>