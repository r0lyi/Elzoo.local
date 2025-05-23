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
    // Este método de instancia podría no usarse directamente en la API REST,
    // pero es útil si creas una instancia de Usuario y luego la guardas.
    public function guardar() {
        $connection = ControllerDatabase::connect();
        $query = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        return $stmt->execute([$this->nombre, $this->email, $this->password, $this->rol]);
    }

    // Métodos estáticos usados por el UsuariosController de la API
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
            $data['password'] ?? '', // Considera que la password NO DEBE guardarse en texto plano. ¡HASHÉALA!
            $data['rol']
        ]);
        return $result ? $connection->lastInsertId() : false;
    }

    public static function update($id, $data) {
        $connection = ControllerDatabase::connect();
         // Construye la query dinámicamente para solo actualizar los campos presentes en $data
         $fields = [];
         $values = [];
         if (isset($data['nombre'])) {
             $fields[] = 'nombre = ?';
             $values[] = $data['nombre'];
         }
         if (isset($data['email'])) {
              // TODO: Considerar verificar si el nuevo email ya existe para otro usuario
             $fields[] = 'email = ?';
             $values[] = $data['email'];
         }
         if (isset($data['rol'])) {
             $fields[] = 'rol = ?';
             $values[] = $data['rol'];
         }
          // Si quisieras actualizar password, necesitarías un método aparte
          // y hashear la nueva password antes de pasarla aquí.

         if (empty($fields)) {
             return false; // No hay campos para actualizar
         }

         $query = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = ?";
         $values[] = $id;

        $stmt = $connection->prepare($query);
        return $stmt->execute($values);
    }

    public static function delete($id) {
        $connection = ControllerDatabase::connect();
        $query = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $connection->prepare($query);
        return $stmt->execute([$id]);
    }

    public static function updatePassword($id, $hashedPassword) {
        $connection = ControllerDatabase::connect();
        $query = "UPDATE usuarios SET password = ? WHERE id = ?";
        $stmt = $connection->prepare($query);
        return $stmt->execute([$hashedPassword, $id]);
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
        // Selecciona solo los campos seguros para la API (omite password y token)
        $query = "SELECT id, nombre, email, rol, fecha_registro FROM usuarios WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Estos métodos de token son útiles para sesiones o restablecimiento de contraseña,
    // pero no son intrínsecos al funcionamiento básico de la API CRUD que hemos diseñado.
    public static function guardarToken($usuarioId, $token) {
        $connection = ControllerDatabase::connect();
        // Asegúrate de que tu tabla 'usuarios' tiene una columna 'token'
        $query = "UPDATE usuarios SET token = ? WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$token, $usuarioId]);
        return $stmt->rowCount() > 0;
    }

    public static function eliminarTokenPorId($id) {
        $connection = ControllerDatabase::connect();
         // Asegúrate de que tu tabla 'usuarios' tiene una columna 'token'
        $query = "UPDATE usuarios SET token = NULL WHERE id = ?";
        $stmt = $connection->prepare($query);
        return $stmt->execute([$id]);
    }
}