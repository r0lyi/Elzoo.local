<?php
// models/Foro.php

require_once __DIR__ . '/../controllers/ControllerDatabase.php';

require_once __DIR__ . '/ComentarioForo.php';
require_once __DIR__ . '/Usuarios.php'; // Asegúrate de que este también esté correcto

class Foro {
    private $id;
    private $titulo;
    private $contenido;
    private $fecha_creacion;
    private $autor_id;

    public function __construct(array $data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function getId() { return $this->id; }
    public function getTitulo() { return $this->titulo; }
    public function getContenido() { return $this->contenido; }
    public function getFechaCreacion() { return $this->fecha_creacion; }
    public function getAutorId() { return $this->autor_id; }

    public static function crear(string $titulo, string $contenido, int $autorId): bool {
        $db = ControllerDatabase::connect();
        $sql = "INSERT INTO foros (titulo, contenido, autor_id) VALUES (:titulo, :contenido, :autor_id)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':titulo'    => $titulo,
            ':contenido' => $contenido,
            ':autor_id'  => $autorId
        ]);
    }

    public static function obtenerTodos(): array {
        $db = ControllerDatabase::connect();
        $stmt = $db->query("SELECT * FROM foros ORDER BY fecha_creacion DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($data) => new Foro($data), $rows);
    }

    public static function obtenerPorId(int $id): ?Foro {
        $db = ControllerDatabase::connect();
        $stmt = $db->prepare("SELECT * FROM foros WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? new Foro($data) : null;
    }

    public function obtenerComentarios(): array {
        return ComentarioForo::obtenerPorForoId($this->id);
    }

    public static function find($id): ?array {
        $db = ControllerDatabase::connect();
        $stmt = $db->prepare("SELECT * FROM foros WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        return $post ?: null;
    }

    public static function findAll(): array {
        $db = ControllerDatabase::connect();
        // Incluye el nombre del autor por defecto al listar todos
        $stmt = $db->query("SELECT f.*, u.nombre as autor_nombre FROM foros f JOIN usuarios u ON f.autor_id = u.id ORDER BY fecha_creacion DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(array $data): int|false {
        $db = ControllerDatabase::connect();
        if (!isset($data['titulo'], $data['contenido'], $data['autor_id'])) {
            return false;
        }
        $sql = "INSERT INTO foros (titulo, contenido, autor_id) VALUES (:titulo, :contenido, :autor_id)";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            ':titulo'    => $data['titulo'],
            ':contenido' => $data['contenido'],
            ':autor_id'  => $data['autor_id']
        ]);
        return $result ? $db->lastInsertId() : false;
    }

    public static function update(int $id, array $data): bool {
         $db = ControllerDatabase::connect();
         $fields = [];
         $values = [];

         if (isset($data['titulo']) && trim($data['titulo']) !== '') {
             $fields[] = 'titulo = ?';
             $values[] = trim($data['titulo']);
         }
         if (isset($data['contenido']) && trim($data['contenido']) !== '') {
             $fields[] = 'contenido = ?';
             $values[] = trim($data['contenido']);
         }

         if (empty($fields)) {
             return false;
         }

         $sql = "UPDATE foros SET " . implode(', ', $fields) . " WHERE id = ?";
         $values[] = $id;

         $stmt = $db->prepare($sql);
         return $stmt->execute($values);
    }

    public static function delete(int $id): bool {
        $db = ControllerDatabase::connect();
        $sql = "DELETE FROM foros WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public static function findWithAuthor($id): ?array {
        $db = ControllerDatabase::connect();
        $sql = "SELECT f.*, u.nombre as autor_nombre, u.rol as autor_rol
                FROM foros f
                JOIN usuarios u ON f.autor_id = u.id
                WHERE f.id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($post) {
             unset($post['password']);
             unset($post['token']);
        }
        return $post ?: null;
    }

    public static function filterWithAuthor(array $criteria = []): array {
        $db = ControllerDatabase::connect();
        $sql = "SELECT f.*, u.nombre as autor_nombre, u.rol as autor_rol
                FROM foros f
                JOIN usuarios u ON f.autor_id = u.id";

        $whereClauses = [];
        $params = [];

        // Filtro por título (búsqueda parcial)
        if (isset($criteria['titulo']) && $criteria['titulo'] !== '') {
            $whereClauses[] = "f.titulo LIKE ?";
            $params[] = '%' . $criteria['titulo'] . '%';
        }
        // Filtro por nombre de autor (búsqueda parcial)
        if (isset($criteria['autor_nombre']) && $criteria['autor_nombre'] !== '') {
            $whereClauses[] = "u.nombre LIKE ?"; // Asegúrate de que la columna de nombre de usuario es 'nombre'
            $params[] = '%' . $criteria['autor_nombre'] . '%';
        }
       


        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $sql .= " ORDER BY f.fecha_creacion DESC"; // Siempre ordenar

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Limpiar datos sensibles como password y token antes de devolver
        foreach ($posts as &$post) {
            unset($post['password']);
            unset($post['token']);
        }
        return $posts;
    }
}