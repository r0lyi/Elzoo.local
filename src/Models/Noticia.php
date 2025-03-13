<?php
namespace Elzoo\Models;

use Elzoo\Core\Database;
use PDO;
use PDOException;

class Noticia {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getNoticiasPaginated(int $limit, int $offset = 0): array {
        try {
            $totalStmt = $this->db->query("SELECT COUNT(*) FROM noticias");
            $total = (int)$totalStmt->fetchColumn();

            $query = "SELECT id, titular, descripcion, imagen 
                      FROM noticias 
                      ORDER BY id DESC 
                      LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
}