<?php
namespace Models;

include_once 'controllers/ControllerDatabase.php'; // Asegúrate de que la ruta sea correcta

class Usuarios {
    private $id;
    private $nombre;
    private $correo;
    private $contraseña_hash;
    private $tipo_usuario;

    // Constructor
    public function __construct($nombre = '', $correo = '', $contraseña_hash = '', $tipo_usuario = 'usuario') {
        $this->nombre = $nombre;
        $this->correo = $correo;
        $this->contraseña_hash = $contraseña_hash;
        $this->tipo_usuario = $tipo_usuario;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getCorreo() {
        return $this->correo;
    }

    public function getContraseñaHash() {
        return $this->contraseña_hash;
    }

    public function getTipoUsuario() {
        return $this->tipo_usuario;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setCorreo($correo) {
        $this->correo = $correo;
    }

    public function setContraseñaHash($contraseña_hash) {
        $this->contraseña_hash = $contraseña_hash;
    }

    public function setTipoUsuario($tipo_usuario) {
        $this->tipo_usuario = $tipo_usuario;
    }

    // Método para almacenar el usuario en la base de datos
    public function guardar() {
        // Obtener la conexión a la base de datos
        $connection = ControllerDatabase::connect();
    
        // Sentencia SQL para insertar el usuario
        $query = "INSERT INTO usuarios (nombre, correo, contraseña_hash, tipo_usuario) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
    
        // Ejecutar la consulta
        return $stmt->execute([$this->nombre, $this->correo, $this->contraseña_hash, $this->tipo_usuario]);
    }
    // Método estático para verificar si un correo ya existe en la base de datos
    public static function verificarCorreoExistente($correo) {
        $connection = ControllerDatabase::connect();
        $query = "SELECT * FROM usuarios WHERE correo = :correo";
        $statement = $connection->prepare($query);
        $statement->bindParam(':correo', $correo);
        $statement->execute();
        return $statement->rowCount() > 0; // Retorna true si existe, false si no
    }
    
    public static function obtenerPorCorreo($correo) {
        $connection = ControllerDatabase::connect();
        $query = "SELECT * FROM usuarios WHERE correo = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function obtenerPorId($id) {
        $connection = ControllerDatabase::connect();
        $query = "SELECT id, nombre, correo, tipo_usuario FROM usuarios WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    
}
?>
