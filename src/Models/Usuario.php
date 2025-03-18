<?php
namespace Elzoo\Models;

use Elzoo\Core\Database;
use PDO;
use PDOException;

class Usuario
{
    private $id;
    private $nombre;
    private $correo;
    private $contrasena;

    private $pdo;

    public function __construct($id, $nombre, $correo, $contrasena, PDO $pdo)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->correo = $correo;
        $this->contrasena = $contrasena;
        $this->pdo = $pdo;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    public function getCorreo()
    {
        return $this->correo;
    }

    public function setCorreo($correo)
    {
        $this->correo = $correo;
    }

    public function getContrasena()
    {
        return $this->contrasena;
    }

    public function setContrasena($contrasena)
    {
        $this->contrasena = $contrasena;
    }

    public static function crearUsuario($nombre, $correo, $contrasena, PDO $pdo)
    {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, contrasena) VALUES (:nombre, :correo, :contrasena)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':contrasena', $contrasena);
        $stmt->execute();

        $id = $pdo->lastInsertId();

        return new Usuario($id, $nombre, $correo, $contrasena, $pdo);
    }

    public static function existeCorreo($correo, PDO $pdo)
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = :correo");
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        $count = $stmt->fetchColumn();

        return $count > 0;
    }
}