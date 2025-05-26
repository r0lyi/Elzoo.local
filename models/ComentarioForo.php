<?php
// models/ComentarioForo.php

require_once __DIR__ . '/../controllers/ControllerDatabase.php';
require_once __DIR__ . '/Usuarios.php'; // Se incluirá Usuarios por si necesitamos datos del autor

class ComentarioForo {
    private $id;
    private $foro_id;
    private $autor_id;
    private $contenido;
    private $fecha_creacion;

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
    public function getForoId() { return $this->foro_id; }
    public function getAutorId() { return $this->autor_id; }
    public function getContenido() { return $this->contenido; }
    public function getFechaCreacion() { return $this->fecha_creacion; }

    // Mantener método 'crear' existente (retorna bool)
    // Crear nuevo comentario (usando parámetros específicos)
  public static function crear(int $foroId, int $autorId, string $contenido): bool {
        $db = ControllerDatabase::connect();
        $sql = "INSERT INTO comentarios_foro (foro_id, autor_id, contenido) VALUES (:foro_id, :autor_id, :contenido)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':foro_id'  => $foroId,
            ':autor_id' => $autorId,
            ':contenido'=> $contenido
        ]);
    }

    public static function obtenerPorForoId(int $foroId): array {
        $db = ControllerDatabase::connect();
        $stmt = $db->prepare("SELECT * FROM comentarios_foro WHERE foro_id = :foro_id ORDER BY fecha_creacion ASC");
        $stmt->execute([':foro_id' => $foroId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // This maps raw data to ComentarioForo objects, which is what we need.
        return array_map(fn($data) => new ComentarioForo($data), $rows);
    }

    // --- Nuevos Métodos estáticos para API CRUD (retornan arrays asociativos o IDs) ---

    // Encontrar un comentario por su ID (retorna array asociativo o null)
    public static function find($id): ?array {
        $db = ControllerDatabase::connect();
        $stmt = $db->prepare("SELECT * FROM comentarios_foro WHERE id = ?");
        $stmt->execute([$id]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
        return $comment ?: null; // Retorna null si no se encuentra
    }

     // Encontrar todos los comentarios (retorna array de arrays asociativos)
     // Útil si quieres un endpoint GET /api/v1/comentarios (pero puede ser ineficiente)
    public static function findAll(): array {
        $db = ControllerDatabase::connect();
        $stmt = $db->query("SELECT * FROM comentarios_foro ORDER BY fecha_creacion ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Encontrar comentarios por ID de foro (retorna array de arrays asociativos)
    // Útil para GET /api/v1/foros/{foro_id}/comentarios
    public static function findByForoId(int $foroId): array {
         $db = ControllerDatabase::connect();
         $stmt = $db->prepare("SELECT * FROM comentarios_foro WHERE foro_id = :foro_id ORDER BY fecha_creacion ASC");
         $stmt->execute([':foro_id' => $foroId]);
         return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna arrays asociativos
    }

    // Crear nuevo comentario a partir de un array de datos (retorna el ID del nuevo comentario o false)
    // Más adecuado para recibir datos JSON en la API
    public static function create(array $data): int|false {
        $db = ControllerDatabase::connect();
        // Validar que los campos necesarios están en $data
        if (!isset($data['foro_id'], $data['autor_id'], $data['contenido'])) {
            // En un modelo más robusto, lanzarías una excepción o loggearías el error.
            // Aquí, para simplicidad, solo devolvemos false.
            return false;
        }
        $sql = "INSERT INTO comentarios_foro (foro_id, autor_id, contenido) VALUES (:foro_id, :autor_id, :contenido)";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            ':foro_id'  => $data['foro_id'],
            ':autor_id' => $data['autor_id'],
            ':contenido'=> $data['contenido']
        ]);

        return $result ? $db->lastInsertId() : false; // Retorna el ID del nuevo comentario si fue exitoso
    }

    // Actualizar un comentario por ID (solo contenido)
    public static function update(int $id, array $data): bool {
         $db = ControllerDatabase::connect();
         // Solo permitimos actualizar el contenido desde la API por defecto
         if (!isset($data['contenido']) || trim($data['contenido']) === '') {
             // No hay contenido válido para actualizar
             return false;
         }

         $sql = "UPDATE comentarios_foro SET contenido = :contenido WHERE id = :id";
         $stmt = $db->prepare($sql);
         return $stmt->execute([
             ':contenido' => trim($data['contenido']),
             ':id' => $id
         ]);
    }

    // Eliminar un comentario por ID
    public static function delete(int $id): bool {
        $db = ControllerDatabase::connect();
        $sql = "DELETE FROM comentarios_foro WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$id]);
    }

    // --- Métodos con datos del autor (Útiles para mostrar en la API) ---

    // Obtener un comentario por ID con datos básicos del autor
    public static function findWithAuthor($id): ?array {
        $db = ControllerDatabase::connect();
        $sql = "SELECT cf.*, u.nombre as autor_nombre, u.rol as autor_rol
                FROM comentarios_foro cf
                JOIN usuarios u ON cf.autor_id = u.id
                WHERE cf.id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
        // Si el JOIN accidentalmente incluyera password o token (lo cual no debería pasar con la query anterior),
        // los eliminamos por seguridad.
        if ($comment) {
             unset($comment['password']);
             unset($comment['token']);
        }
        return $comment ?: null;
    }

    // Obtener comentarios por ID de foro con datos básicos del autor
     public static function findByForoIdWithAuthor(int $foroId): array {
         $db = ControllerDatabase::connect();
         $sql = "SELECT cf.*, u.nombre as autor_nombre, u.rol as autor_rol
                 FROM comentarios_foro cf
                 JOIN usuarios u ON cf.autor_id = u.id
                 WHERE cf.foro_id = :foro_id
                 ORDER BY cf.fecha_creacion ASC";
         $stmt = $db->prepare($sql);
         $stmt->execute([':foro_id' => $foroId]);
         $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

         // Limpiar datos sensibles del autor si se incluyeran por error
         foreach ($comments as &$comment) { // Usamos referencia para modificar el array directamente
              unset($comment['password']);
              unset($comment['token']);
         }
         return $comments;
    }
}