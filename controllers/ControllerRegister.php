<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../models/Usuarios.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Función para renderizar la vista de registro
function renderRegisterView($mensaje = null) {
    $loader = new FilesystemLoader(__DIR__ . '/../views');
    $twig = new Environment($loader);

    echo $twig->render('register.html.twig', ['mensaje' => $mensaje]);
}

function register() {
    $mensaje = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'];
        $correo = $_POST['correo'];
        $contraseña = $_POST['contraseña'];
        $tipo_usuario = $_POST['tipo_usuario'] ?? 'usuario';

        if (Usuarios::verificarCorreoExistente($correo)) {
            $mensaje = ['tipo' => 'alert-danger', 'texto' => 'El correo ya está registrado.'];
        } else {
            $contraseña_hash = password_hash($contraseña, PASSWORD_DEFAULT);
            $usuario = new Usuarios($nombre, $correo, $contraseña_hash, $tipo_usuario);

            if ($usuario->guardar()) {
                $mensaje = ['tipo' => 'alert-success', 'texto' => 'Usuario registrado con éxito.'];
            } else {
                $mensaje = ['tipo' => 'alert-danger', 'texto' => 'Hubo un error al registrar el usuario.'];
            }
        }
    }

    renderRegisterView($mensaje);
}

register();

