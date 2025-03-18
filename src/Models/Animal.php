<?php
namespace Elzoo\Models;

use Elzoo\Core\Database;
use PDO;
use PDOException;

class Animal {
    // Atributos de la clase
    private $id;
    private $especie;
    private $imagen;

    // Conexión a la base de datos
    private $db;

    public function __construct() {
        $this->db = Database::getInstance(); // Obtiene la conexión a la base de datos
    }

    // Getters y Setters
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getEspecie() {
        return $this->especie;
    }

    public function setEspecie($especie) {
        $this->especie = $especie;
    }

    public function getImagen() {
        return $this->imagen;
    }

    public function setImagen($imagen) {
        $this->imagen = $imagen;
    }

    /**
     * Obtiene todos los animales de la base de datos.
     *
     * @return array
     */
    public function getAll() {
        try {
            $query = "SELECT * FROM animales";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener los animales: " . $e->getMessage());
        }
    }

    /**
     * Obtiene un animal por su ID y lo carga en los atributos de la clase.
     *
     * @param int $id
     * @return Animal|null
     */
    public function getById($id) {
        try {
            $query = "SELECT * FROM animales WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $animalData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($animalData) {
                $this->setId($animalData['id']);
                $this->setEspecie($animalData['especie']);
                $this->setImagen($animalData['imagen']);
                return $this;
            }

            return null;
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener el animal: " . $e->getMessage());
        }
    }

    /**
     * Inserta un nuevo animal en la base de datos.
     *
     * @return int
     */
    public function insert() {
        try {
            $query = "INSERT INTO animales (especie, imagen) VALUES (:especie, :imagen)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':especie', $this->especie, PDO::PARAM_STR);
            $stmt->bindParam(':imagen', $this->imagen, PDO::PARAM_STR);
            $stmt->execute();
            return $this->db->lastInsertId(); // Devuelve el ID del nuevo animal insertado
        } catch (PDOException $e) {
            throw new PDOException("Error al insertar el animal: " . $e->getMessage());
        }
    }

    /**
     * Actualiza un animal existente en la base de datos.
     *
     * @return bool
     */
    public function update() {
        try {
            $query = "UPDATE animales SET especie = :especie, imagen = :imagen WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindParam(':especie', $this->especie, PDO::PARAM_STR);
            $stmt->bindParam(':imagen', $this->imagen, PDO::PARAM_STR);
            return $stmt->execute(); // Devuelve true si la actualización fue exitosa
        } catch (PDOException $e) {
            throw new PDOException("Error al actualizar el animal: " . $e->getMessage());
        }
    }

    /**
     * Elimina un animal de la base de datos.
     *
     * @return bool
     */
    public function delete() {
        try {
            $query = "DELETE FROM animales WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            return $stmt->execute(); // Devuelve true si la eliminación fue exitosa
        } catch (PDOException $e) {
            throw new PDOException("Error al eliminar el animal: " . $e->getMessage());
        }
    }
}