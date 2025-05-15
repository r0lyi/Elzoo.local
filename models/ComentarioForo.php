<?php

require_once __DIR__ . '/../controllers/ControllerDatabase.php';

class ComentarioForo {
    private $id;
    private $foro_id;
    private $autor_id;
    private $contenido;
    private $fecha_creacion;

    public function __construct(array $data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    // Getters
    public function getId() { return $this->id; }
    public function getForoId() { return $this->foro_id; }
    public function getAutorId() { return $this->autor_id; }
    public function getContenido() { return $this->contenido; }
    public function getFechaCreacion() { return $this->fecha_creacion; }

    // Crear nuevo comentario
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

    // Obtener comentarios por foro
    public static function obtenerPorForoId(int $foroId): array {
        $db = ControllerDatabase::connect();
        $stmt = $db->prepare("SELECT * FROM comentarios_foro WHERE foro_id = :foro_id ORDER BY fecha_creacion ASC");
        $stmt->execute([':foro_id' => $foroId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($data) => new ComentarioForo($data), $rows);
    }
}