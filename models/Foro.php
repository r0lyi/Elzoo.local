<?php

// models/Foro.php
require_once __DIR__ . '/../controllers/ControllerDatabase.php';

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

    // Getters
    public function getId() { return $this->id; }
    public function getTitulo() { return $this->titulo; }
    public function getContenido() { return $this->contenido; }
    public function getFechaCreacion() { return $this->fecha_creacion; }
    public function getAutorId() { return $this->autor_id; }

    // Crear nueva publicación de foro
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

    // Obtener todas las publicaciones
    public static function obtenerTodos(): array {
        $db = ControllerDatabase::connect();
        $stmt = $db->query("SELECT * FROM foros ORDER BY fecha_creacion DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($data) => new Foro($data), $rows);
    }

    // Obtener publicación por ID
    public static function obtenerPorId(int $id): ?Foro {
        $db = ControllerDatabase::connect();
        $stmt = $db->prepare("SELECT * FROM foros WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? new Foro($data) : null;
    }

    // Obtener comentarios asociados
    public function obtenerComentarios(): array {
        return ComentarioForo::obtenerPorForoId($this->id);
    }
}