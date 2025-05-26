<?php

namespace Models;

use Controllers\ControllerDatabase;
use PDO;

require_once __DIR__ . '/../controllers/ControllerDatabase.php';

class Noticias {
    // Atributos
    private $id;
    private $titulo;
    private $descripcion;
    private $fecha_publicacion;
    private $url_origen;
    private $imagen;
    private $autor_id;

    // Constructor
    public function __construct(
        $id = null,
        $titulo = '',
        $descripcion = '',
        $fecha_publicacion = null,
        $url_origen = '',
        $imagen = '',
        $autor_id = null
    ) {
        $this->id = $id;
        $this->titulo = $titulo;
        $this->descripcion = $descripcion;
        $this->fecha_publicacion = $fecha_publicacion;
        $this->url_origen = $url_origen;
        $this->imagen = $imagen;
        $this->autor_id = $autor_id;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getTitulo() { return $this->titulo; }
    public function getDescripcion() { return $this->descripcion; }
    public function getFechaPublicacion() { return $this->fecha_publicacion; }
    public function getUrlOrigen() { return $this->url_origen; }
    public function getImagen() { return $this->imagen; }
    public function getAutorId() { return $this->autor_id; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setTitulo($titulo) { $this->titulo = $titulo; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
    public function setFechaPublicacion($fecha) { $this->fecha_publicacion = $fecha; }
    public function setUrlOrigen($url) { $this->url_origen = $url; }
    public function setImagen($imagen) { $this->imagen = $imagen; }
    public function setAutorId($autor_id) { $this->autor_id = $autor_id; }

    // Obtener todas las noticias
    public static function getNoticias() {
        $db = ControllerDatabase::connect();

        if ($db === null) {
            return [];
        }

        $stmt = $db->query("SELECT * FROM noticias ORDER BY fecha_publicacion DESC");
        $noticiasData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $noticias = [];
        foreach ($noticiasData as $data) {
            $noticia = new self(
                $data['id'],
                $data['titulo'],
                $data['descripcion'],
                $data['fecha_publicacion'],
                $data['url_origen'],
                $data['imagen'],
                $data['autor_id']
            );
            $noticias[] = $noticia;
        }

        return $noticias;
    }
}
