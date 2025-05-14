<?php
namespace Controllers;
use PDO;
use PDOException;
// Clase Singleton para la conexión a la base de datos
class ControllerDatabase {

    private static $host = "localhost"; // Cambia según tu configuración
    private static $username = "usuario"; // Cambia según tu configuración
    private static $password = "CASA%rsg2005"; // Cambia según tu configuración
    private static $dbname = "Elzoo"; // Cambia según el nombre de tu base de datos
    private static $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    );

    // Instancia de la clase
    private static $instance = null;

    // Constructor privado para evitar instanciación externa
    private function __construct() {
        // El proceso costoso de la conexión a la base de datos ocurre aquí
    }

    // Obtener la instancia de la clase
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new ControllerDatabase();
        }
        return self::$instance;
    }

    // Conexión a la base de datos
    public static function connect() {
        try {
            $connection = new PDO('mysql:host=' . self::$host . ';dbname=' . self::$dbname, 
                                  self::$username, self::$password, self::$options);
            return $connection;
        } catch(PDOException $error) {
            echo "Error de conexión: " . $error->getMessage();
            return null;
        }
    }
}
