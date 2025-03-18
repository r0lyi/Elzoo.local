<?php
namespace Elzoo\Models;

use Elzoo\Core\Database;
use PDO;
use PDOException;

class Noticia {
    // Atributos de la clase
    private $id;
    private $titular;
    private $descripcion;
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

    public function getTitular() {
        return $this->titular;
    }

    public function setTitular($titular) {
        $this->titular = $titular;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function getImagen() {
        return $this->imagen;
    }

    public function setImagen($imagen) {
        $this->imagen = $imagen;
    }

    /**
     * Obtiene noticias paginadas de la base de datos.
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getNoticiasPaginated(int $limit, int $offset = 0): array {
        try {
            // Obtener el total de noticias
            $totalStmt = $this->db->query("SELECT COUNT(*) FROM noticias");
            $total = (int)$totalStmt->fetchColumn();

            // Obtener noticias paginadas
            $query = "SELECT id, titular, descripcion, imagen 
                      FROM noticias 
                      ORDER BY id DESC 
                      LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Verificar si hay más noticias
            $hasMore = ($offset + count($noticias)) < $total;

            return [
                'noticias' => $noticias,
                'total' => $total,
                'hasMore' => $hasMore
            ];
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener noticias: " . $e->getMessage());
        }
    }

    /**
     * Obtiene una noticia por su ID y la carga en los atributos de la clase.
     *
     * @param int $id
     * @return Noticia|null
     */
    public function getById($id) {
        try {
            $query = "SELECT id, titular, descripcion, imagen FROM noticias WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $noticiaData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($noticiaData) {
                $this->setId($noticiaData['id']);
                $this->setTitular($noticiaData['titular']);
                $this->setDescripcion($noticiaData['descripcion']);
                $this->setImagen($noticiaData['imagen']);
                return $this;
            }

            return null;
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener la noticia: " . $e->getMessage());
        }
    }

    /**
     * Inserta una nueva noticia en la base de datos.
     *
     * @return int
     */
    public function insert() {
        try {
            $query = "INSERT INTO noticias (titular, descripcion, imagen) VALUES (:titular, :descripcion, :imagen)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':titular', $this->titular, PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $this->descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':imagen', $this->imagen, PDO::PARAM_STR);
            $stmt->execute();
            return $this->db->lastInsertId(); // Devuelve el ID de la nueva noticia insertada
        } catch (PDOException $e) {
            throw new PDOException("Error al insertar la noticia: " . $e->getMessage());
        }
    }

    /**
     * Actualiza una noticia existente en la base de datos.
     *
     * @return bool
     */
    public function update() {
        try {
            $query = "UPDATE noticias SET titular = :titular, descripcion = :descripcion, imagen = :imagen WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindParam(':titular', $this->titular, PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $this->descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':imagen', $this->imagen, PDO::PARAM_STR);
            return $stmt->execute(); // Devuelve true si la actualización fue exitosa
        } catch (PDOException $e) {
            throw new PDOException("Error al actualizar la noticia: " . $e->getMessage());
        }
    }

    /**
     * Elimina una noticia de la base de datos.
     *
     * @return bool
     */
    public function delete() {
        try {
            $query = "DELETE FROM noticias WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            return $stmt->execute(); // Devuelve true si la eliminación fue exitosa
        } catch (PDOException $e) {
            throw new PDOException("Error al eliminar la noticia: " . $e->getMessage());
        }
    }
}