<?php
namespace Elzoo\Core;

use PDO;
use PDOException;

class Database {
    private static $host = "localhost";
    private static $username = "usuario";
    private static $password = "usuario";
    private static $dbname = "Elzoo";
    private static $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    private static $instance = null;
    private static $connection = null;

    private function __construct() {
        // Constructor privado para evitar instanciación directa
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$connection = self::connect();
        }
        return self::$connection; // Devuelve la conexión PDO directamente
    }

    private static function connect() {
        try {
            return new PDO(
                "mysql:host=" . self::$host . ";dbname=" . self::$dbname,
                self::$username,
                self::$password,
                self::$options
            );
        } catch (PDOException $e) {
            // En lugar de echo, lanza una excepción o registra el error
            throw new PDOException("Error de conexión: " . $e->getMessage());
        }
    }
}