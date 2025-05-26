<?php

include_once __DIR__ . '/../controllers/ControllerDatabase.php';

class Noticias {
    // Atributos
    private $id;
    private $titulo;
    private $descripcion;
    private $fecha_publicacion; // Asumimos que es un campo DATE o DATETIME en la BD
    private $url_origen;
    private $imagen; // Path o URL
    // El atributo autor_id ha sido eliminado

    // Constructor - Actualizado para eliminar el parámetro autor_id
    public function __construct(
        $id = null,
        $titulo = '',
        $descripcion = '',
        $fecha_publicacion = null,
        $url_origen = '',
        $imagen = ''
        // El parámetro $autor_id ha sido eliminado
    ) {
        $this->id = $id;
        $this->titulo = $titulo;
        $this->descripcion = $descripcion;
        $this->fecha_publicacion = $fecha_publicacion;
        $this->url_origen = $url_origen;
        $this->imagen = $imagen;
        // La inicialización del atributo autor_id ha sido eliminada
    }

    // Getters - El getter getAutorId() ha sido eliminado
    public function getId() { return $this->id; }
    public function getTitulo() { return $this->titulo; }
    public function getDescripcion() { return $this->descripcion; }
    public function getFechaPublicacion() { return $this->fecha_publicacion; }
    public function getUrlOrigen() { return $this->url_origen; }
    public function getImagen() { return $this->imagen; }
    // El método getAutorId() ha sido eliminado

    // Setters - El setter setAutorId() ha sido eliminado
    public function setId($id) { $this->id = $id; }
    public function setTitulo($titulo) { $this->titulo = $titulo; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
    public function setFechaPublicacion($fecha) { $this->fecha_publicacion = $fecha; }
    public function setUrlOrigen($url) { $this->url_origen = $url; }
    public function setImagen($imagen) { $this->imagen = $imagen; } // Nota: la subida/gestión de archivos es compleja
    // El método setAutorId() ha sido eliminado

    // Mantener método listNoticias existente (retorna OBJETOS Noticias) - Actualizado para reflejar la ausencia de autor_id
    public static function listNoticias($limit = 12, $offset = 0) { // Default to 7 news per page
        $db = ControllerDatabase::connect();
        if ($db === null) {
            return [];
        }

        // Use LIMIT and OFFSET in the SQL query for pagination
        $stmt = $db->prepare("SELECT id, titulo, descripcion, fecha_publicacion, url_origen, imagen FROM noticias ORDER BY fecha_publicacion DESC LIMIT :limit OFFSET :offset");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $noticiasData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $noticias = [];
        foreach ($noticiasData as $noticiaData) {
            $noticia = new Noticias(
                $noticiaData['id'],
                $noticiaData['titulo'],
                $noticiaData['descripcion'],
                $noticiaData['fecha_publicacion'],
                $noticiaData['url_origen'],
                $noticiaData['imagen']
            );
            $noticias[] = $noticia;
        }

        return $noticias;
    }

    public static function getTotalNoticiasCount() {
        $db = ControllerDatabase::connect();
        if ($db === null) {
            return 0;
        }

        $stmt = $db->query("SELECT COUNT(*) FROM noticias");
        return (int) $stmt->fetchColumn(); // Cast to int
    }

 
    public static function find($id): ?array {
        $connection = ControllerDatabase::connect();
        // Eliminar autor_id de la consulta SELECT
        $query = "SELECT id, titulo, descripcion, fecha_publicacion, url_origen, imagen FROM noticias WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$id]);
        $news = $stmt->fetch(PDO::FETCH_ASSOC);
        return $news ?: null; // Retorna null si no se encuentra
    }


    public static function findAll(): array {
        $connection = ControllerDatabase::connect();
        // Eliminar autor_id de la consulta SELECT
        $query = "SELECT id, titulo, descripcion, fecha_publicacion, url_origen, imagen FROM noticias ORDER BY fecha_publicacion DESC";
        $stmt = $connection->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getNoticias() {
        return self::findAll();
    }


    public static function create(array $data): int|false {
        $connection = ControllerDatabase::connect();

        if (!isset($data['titulo'], $data['descripcion'], $data['fecha_publicacion'], $data['url_origen'], $data['imagen'])) {
             // En un código robusto, se debería loggear o lanzar una excepción más específica.
             error_log("Error en Noticias::create: Faltan campos de datos requeridos para la firma del método (sin autor_id).");
             return false;
        }

        // Eliminar autor_id de la consulta INSERT y los placeholders/valores
        $query = "INSERT INTO noticias (titulo, descripcion, fecha_publicacion, url_origen, imagen)
                  VALUES (?, ?, ?, ?, ?)"; // 5 placeholders ahora
        $stmt = $connection->prepare($query);

        try {
             $result = $stmt->execute([
                $data['titulo'],
                $data['descripcion'],
                $data['fecha_publicacion'],
                $data['url_origen'],
                $data['imagen']
                // El valor de autor_id ha sido eliminado
            ]);
            if ($result) {
                return $connection->lastInsertId(); // Retorna el nuevo ID
            }
            return false; // Indica fallo
        } catch (PDOException $e) {
             // Loggear el error real de la base de datos para depuración
             error_log("Database error en Noticias::create (sin autor_id): " . $e->getMessage());
             return false; // Indica fallo
        }
    }


    public static function update($id, array $data): bool {
        $connection = ControllerDatabase::connect();

         // Define todos los campos posibles que se pueden actualizar (elminado autor_id)
         // Asegurarse de que 'id' NO esté en esta lista
         $allowedFields = ['titulo', 'descripcion', 'fecha_publicacion', 'url_origen', 'imagen'];

         $updateFields = [];
         $values = [];

         foreach ($allowedFields as $field) {

            if (isset($data[$field])) {
                $updateFields[] = "`{$field}` = :{$field}"; // Usar placeholders nombrados para claridad y seguridad
                $values[":{$field}"] = $data[$field]; // Almacenar el valor asociado con el placeholder
            }
         }

         if (empty($updateFields)) {
             // No se proporcionaron campos válidos en el array de datos para actualizar
             // Loggear una advertencia para depuración
             error_log("Database warning en Noticias::update (sin autor_id): No se proporcionaron campos válidos para actualizar el ID {$id}.");
             return false; // No hay campos para actualizar
         }

         // Construir la consulta UPDATE dinámicamente
         $sql = "UPDATE noticias SET " . implode(', ', $updateFields) . " WHERE id = :id";
         $values[":id"] = $id; // Añadir el ID al array de valores

         $stmt = $connection->prepare($sql);

         try {
             // execute devuelve true si la consulta se ejecutó sin errores de DB (incluso si 0 filas afectadas)
             return $stmt->execute($values);
         } catch (PDOException $e) {
             // Loggear el error real de la base de datos
             error_log("Database error en Noticias::update (ID: {$id}, sin autor_id): " . $e->getMessage());
             return false; // Indica fallo
         }
    }


    public static function delete($id): bool {
        $connection = ControllerDatabase::connect();
        $query = "DELETE FROM noticias WHERE id = ?";
        $stmt = $connection->prepare($query);
        try {
            // execute devuelve true en éxito (incluso si 0 filas afectadas), false en caso de fallo
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
             // Loggear el error de base de datos (importante para depurar, especialmente si es una violación de FK)
            error_log("Database error en Noticias::delete (ID: {$id}, sin autor_id): " . $e->getMessage());
             // Retornar false en caso de cualquier error de base de datos (incluida violación de restricción de integridad)
            return false; // Indica fallo (posiblemente debido a FK)
        }
    }


}