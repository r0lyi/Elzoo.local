<?php
// models/Usuarios.php
require_once __DIR__ . '/../controllers/ControllerDatabase.php';

class Usuarios {
    private $id;
    private $nombre;
    private $email;
    private $password;
    private $rol;
    private $fecha_registro;

    public function __construct($nombre = '', $email = '', $password = '', $rol = 'usuario') {
        $this->nombre = $nombre;
        $this->email = $email;
        $this->password = $password;
        $this->rol = $rol;
    }

    // Getters y Setters
    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function getEmail() { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getRol() { return $this->rol; }
    public function getFechaRegistro() { return $this->fecha_registro; }

    public function setId($id) { $this->id = $id; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setEmail($email) { $this->email = $email; }
    public function setPassword($password) { $this->password = $password; }
    public function setRol($rol) { $this->rol = $rol; }

    // Guardar el usuario en la base de datos
    public function guardar() {
        $connection = ControllerDatabase::connect();
        $query = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        return $stmt->execute([$this->nombre, $this->email, $this->password, $this->rol]);
    }

    // Métodos estáticos
    public static function find($id) {
        $connection = ControllerDatabase::connect();
        $query = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findAll() {
        $connection = ControllerDatabase::connect();
        $query = "SELECT * FROM usuarios";
        $stmt = $connection->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $connection = ControllerDatabase::connect();
        $query = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        $result = $stmt->execute([
            $data['nombre'], 
            $data['email'], 
            $data['password'] ?? '', 
            $data['rol']
        ]);
        return $result ? $connection->lastInsertId() : false;
    }

    public static function update($id, $data) {
        $connection = ControllerDatabase::connect();
        $query = "UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id = ?";
        $stmt = $connection->prepare($query);
        return $stmt->execute([
            $data['nombre'], 
            $data['email'], 
            $data['rol'], 
            $id
        ]);
    }

    public static function delete($id) {
        $connection = ControllerDatabase::connect();
        $query = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $connection->prepare($query);
        return $stmt->execute([$id]);
    }

    public static function verificarCorreoExistente($email) {
        $connection = ControllerDatabase::connect();
        $query = "SELECT * FROM usuarios WHERE email = :email";
        $statement = $connection->prepare($query);
        $statement->bindParam(':email', $email);
        $statement->execute();
        return $statement->rowCount() > 0;
    }

    public static function obtenerPorCorreo($email) {
        $connection = ControllerDatabase::connect();
        $query = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function obtenerPorId($id) {
        $connection = ControllerDatabase::connect();
        $query = "SELECT id, nombre, email, rol FROM usuarios WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Puedes omitir si no usas token, ya que tu tabla no tiene ese campo
    public static function guardarToken($usuarioId, $token) {
        $connection = ControllerDatabase::connect();
        $query = "UPDATE usuarios SET token = ? WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$token, $usuarioId]);
        return $stmt->rowCount() > 0;
    }

    public static function eliminarTokenPorId($id) {
        $connection = ControllerDatabase::connect();
        $query = "UPDATE usuarios SET token = NULL WHERE id = ?";
        $stmt = $connection->prepare($query);
        return $stmt->execute([$id]);
    }
}
?>
