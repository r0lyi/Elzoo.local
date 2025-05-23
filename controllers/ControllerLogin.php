<?php
session_start();

require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../controllers/ControllerJWT.php';
require_once __DIR__ . '/../controllers/ControllerCookie.php';
require_once __DIR__ . '/../controllers/ControllerTwig.php';

// Función principal de login
function login() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $correo = $_POST['correo'] ?? '';
        $contraseña = $_POST['contraseña'] ?? '';

        // Validar entrada
        if (empty($correo) || empty($contraseña)) {
            renderView('login.html.twig', ['error' => 'Debes introducir el correo y la contraseña.']);
            return;
        }

        // Obtener usuario por correo
        $usuario = Usuarios::obtenerPorCorreo($correo);

        // Verificar si existe y tiene el campo correcto
        if ($usuario && isset($usuario['password']) && password_verify($contraseña, $usuario['password'])) {

            // Header del token JWT
            $header = [
                'alg' => 'HS256',
                'typ' => 'JWT'
            ];

            // Payload con datos del usuario
            $payload = [
                'sub' => $usuario['id'],
               // 'exp' => time() + 3600 // 1 hora
            ];

            // Generar y guardar el token
            $jwt = generarJWT($header, $payload, 'mi_clave_secreta');
            Usuarios::guardarToken($usuario['id'], $jwt);

            // Guardar el token en cookie
            setAuthCookie($jwt, time() + 3600);

            // Redirigir a home
            header('Location: /home');
            exit;
        } else {
            // Usuario o contraseña incorrectos
            renderView('login.html.twig', ['error' => 'Correo o contraseña incorrectos.']);
        }
    } else {
        // Mostrar formulario si no es POST
        renderView('login.html.twig');
    }
}

login();
