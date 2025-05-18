<?php
// models/Foro.php

require_once __DIR__ . '/../controllers/ControllerDatabase.php';
require_once __DIR__ . '/ComentarioForo.php'; // Necesario porque Foro::obtenerComentarios lo usa

class Foro {
    private $id;
    private $titulo;
    private $contenido;
    private $fecha_creacion;
    private $autor_id;

    // Mantener __construct existente
    public function __construct(array $data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    // Mantener Getters existentes
    public function getId() { return $this->id; }
    public function getTitulo() { return $this->titulo; }
    public function getContenido() { return $this->contenido; }
    public function getFechaCreacion() { return $this->fecha_creacion; }
    public function getAutorId() { return $this->autor_id; }

    // Mantener método 'crear' existente (retorna bool)
    public static function crear(string $titulo, string $contenido, int $autorId): bool {
        $db = ControllerDatabase::connect();
        $sql = "INSERT INTO foros (titulo, contenido, autor_id) VALUES (:titulo, :contenido, :autor_id)";
        $stmt = $db->prepare($sql);
        // Nota: Este método no retorna el nuevo ID
        return $stmt->execute([
            ':titulo'    => $titulo,
            ':contenido' => $contenido,
            ':autor_id'  => $autorId
        ]);
    }

    // Mantener método 'obtenerTodos' existente (retorna OBJETOS Foro)
    public static function obtenerTodos(): array {
        $db = ControllerDatabase::connect();
        $stmt = $db->query("SELECT * FROM foros ORDER BY fecha_creacion DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($data) => new Foro($data), $rows);
    }

    // Mantener método 'obtenerPorId' existente (retorna OBJETO Foro)
    public static function obtenerPorId(int $id): ?Foro {
        $db = ControllerDatabase::connect();
        $stmt = $db->prepare("SELECT * FROM foros WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? new Foro($data) : null;
    }

    // Mantener método 'obtenerComentarios' existente (retorna OBJETOS ComentarioForo)
    public function obtenerComentarios(): array {
        // Este método usa ComentarioForo::obtenerPorForoId, que retorna objetos.
        // Si necesitas comentarios como arrays asociativos para la API,
        // el controlador API de Foro puede usar ComentarioForo::findByForoId() o findByForoIdWithAuthor().
        return ComentarioForo::obtenerPorForoId($this->id);
    }

    // --- Nuevos Métodos estáticos para API CRUD (retornan arrays asociativos o IDs) ---

    // Encontrar un post de foro por su ID (retorna array asociativo o null)
    // Versión API-friendly de obtenerPorId
    public static function find($id): ?array {
        $db = ControllerDatabase::connect();
        $stmt = $db->prepare("SELECT * FROM foros WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        return $post ?: null; // Retorna null si no se encuentra
    }

     // Encontrar todos los posts de foro (retorna array de arrays asociativos)
     // Versión API-friendly de obtenerTodos
    public static function findAll(): array {
        $db = ControllerDatabase::connect();
        // Ordenar por fecha de creación descendente como en obtenerTodos
        $stmt = $db->query("SELECT * FROM foros ORDER BY fecha_creacion DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna arrays asociativos
    }

    // Crear nuevo post de foro a partir de un array de datos (retorna el ID del nuevo post o false)
    // Más adecuado para recibir datos JSON en la API
    public static function create(array $data): int|false {
        $db = ControllerDatabase::connect();
        // Validar que los campos necesarios están en $data
        if (!isset($data['titulo'], $data['contenido'], $data['autor_id'])) {
            // En un modelo más robusto, lanzarías una excepción o loggearías el error.
            // Aquí, para simplicidad, solo devolvemos false.
            return false;
        }
        $sql = "INSERT INTO foros (titulo, contenido, autor_id) VALUES (:titulo, :contenido, :autor_id)";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            ':titulo'    => $data['titulo'],
            ':contenido' => $data['contenido'],
            ':autor_id'  => $data['autor_id']
        ]);

        return $result ? $db->lastInsertId() : false; // Retorna el ID del nuevo post si fue exitoso
    }

    // Actualizar un post de foro por ID (titulo, contenido)
    public static function update(int $id, array $data): bool {
         $db = ControllerDatabase::connect();
         // Construir la consulta dinámicamente basada en los campos proporcionados en $data
         $fields = [];
         $values = [];

         // Validar que los campos que se van a actualizar no estén vacíos si se proporcionan
         if (isset($data['titulo']) && trim($data['titulo']) !== '') {
             $fields[] = 'titulo = ?';
             $values[] = trim($data['titulo']);
         }
         if (isset($data['contenido']) && trim($data['contenido']) !== '') {
             $fields[] = 'contenido = ?';
             $values[] = trim($data['contenido']);
         }

         if (empty($fields)) {
             return false; // No hay campos válidos para actualizar
         }

         $sql = "UPDATE foros SET " . implode(', ', $fields) . " WHERE id = ?";
         $values[] = $id; // Añadir el ID al final del array de valores

         $stmt = $db->prepare($sql);
         return $stmt->execute($values);
    }

    // Eliminar un post de foro por ID
    public static function delete(int $id): bool {
        $db = ControllerDatabase::connect();
        $sql = "DELETE FROM foros WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$id]);
    }

    // --- Nuevos Métodos con datos del autor (Útiles para mostrar en la API) ---

    // Obtener post de foro por ID con datos básicos del autor
    public static function findWithAuthor($id): ?array {
        $db = ControllerDatabase::connect();
        $sql = "SELECT f.*, u.nombre as autor_nombre, u.rol as autor_rol
                FROM foros f
                JOIN usuarios u ON f.autor_id = u.id
                WHERE f.id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        // Limpiar datos sensibles del autor si se incluyeran por error
        if ($post) {
             unset($post['password']);
             unset($post['token']);
        }
        return $post ?: null;
    }

     // Obtener todos los posts de foro con datos básicos del autor
    public static function findAllWithAuthor(): array {
        $db = ControllerDatabase::connect();
        // Usamos query ya que no hay parámetros para bindear, y ordenamos
        $sql = "SELECT f.*, u.nombre as autor_nombre, u.rol as autor_rol
                FROM foros f
                JOIN usuarios u ON f.autor_id = u.id
                ORDER BY f.fecha_creacion DESC";
        $stmt = $db->query($sql);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

         // Limpiar datos sensibles del autor si se incluyeran por error
         foreach ($posts as &$post) { // Usamos referencia para modificar el array directamente
              unset($post['password']);
              unset($post['token']);
         }
         return $posts;
    }
}