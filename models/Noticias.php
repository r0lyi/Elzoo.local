<?php
include_once __DIR__ . '/../controllers/ControllerDatabase.php';

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

    // Obtener una lista de todas las noticias como objetos
    public static function listNoticias() {
        $db = ControllerDatabase::connect();
        if ($db === null) {
            return [];
        }

        $stmt = $db->query("SELECT * FROM noticias ORDER BY fecha_publicacion DESC");
        $noticiasData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $noticias = [];
        foreach ($noticiasData as $noticiaData) {
            $noticia = new Noticias(
                $noticiaData['id'],
                $noticiaData['titulo'],
                $noticiaData['descripcion'],
                $noticiaData['fecha_publicacion'],
                $noticiaData['url_origen'],
                $noticiaData['imagen'],
                $noticiaData['autor_id']
            );
            $noticias[] = $noticia;
        }

        return $noticias;
    }

    // Buscar una noticia por ID y devolver como array
    public static function find($id) {
        $connection = ControllerDatabase::connect();
        $query = "SELECT * FROM noticias WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Buscar todas las noticias como array (alternativa a listNoticias)
    public static function findAll() {
        $connection = ControllerDatabase::connect();
        $query = "SELECT * FROM noticias ORDER BY fecha_publicacion DESC";
        $stmt = $connection->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Alias para compatibilidad
    public static function getNoticias() {
        return self::findAll();
    }

    // Crear una nueva noticia
    public static function create($data) {
        $connection = ControllerDatabase::connect();
        $query = "INSERT INTO noticias (titulo, descripcion, fecha_publicacion, url_origen, imagen, autor_id)
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $result = $stmt->execute([
            $data['titulo'],
            $data['descripcion'],
            $data['fecha_publicacion'],
            $data['url_origen'],
            $data['imagen'],
            $data['autor_id']
        ]);
        if ($result) {
            return $connection->lastInsertId();
        }
        return false;
    }

    // Actualizar una noticia existente
    public static function update($id, $data) {
        $connection = ControllerDatabase::connect();
        $query = "UPDATE noticias SET titulo = ?, descripcion = ?, fecha_publicacion = ?, url_origen = ?, imagen = ?, autor_id = ?
                  WHERE id = ?";
        $stmt = $connection->prepare($query);
        return $stmt->execute([
            $data['titulo'],
            $data['descripcion'],
            $data['fecha_publicacion'],
            $data['url_origen'],
            $data['imagen'],
            $data['autor_id'],
            $id
        ]);
    }

    // Eliminar una noticia por ID
    public static function delete($id) {
        $connection = ControllerDatabase::connect();
        $query = "DELETE FROM noticias WHERE id = ?";
        $stmt = $connection->prepare($query);
        return $stmt->execute([$id]);
    }
}
