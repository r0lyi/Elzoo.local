<?php

require_once __DIR__ . '/../controllers/ControllerJWT.php';
require_once __DIR__ . '/../controllers/ControllerCookie.php';
require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/../controllers/ControllerTwig.php';

function mostrarPerfil() {
    // Obtener el JWT desde la cookie
    $jwt = getAuthCookie();

    // Decodificar el JWT y obtener los datos del usuario
    $datos = decodificarJWT($jwt);

    // Si el JWT o los datos son inválidos, redirigir al home
    if (!$jwt || !$datos || !isset($datos['sub'])) {
        header('Location: /home');
        exit;
    }

    // Obtener la información del usuario desde la base de datos usando su ID
    $usuario = Usuarios::obtenerPorId($datos['sub']);

    // Si no se encuentra el usuario, redirigir al home
    if (!$usuario) {
        header('Location: /home');
        exit;
    }

    renderView('perfil.html.twig', ['usuario' => $usuario]);

    // Renderizar la vista perfil con los datos del usuario
    
}

function cerrarSesion() {

    // Obtener el JWT desde la cookie
    $jwt = getAuthCookie();

    // Si el JWT existe, eliminar el token de la base de datos y la cookie
    if ($jwt) {
        $datos = decodificarJWT($jwt);

        if ($datos && isset($datos['sub'])) {
            // Eliminar el token de la base de datos
            $resultado = Usuarios::eliminarTokenPorId($datos['sub']);
        }

        // Eliminar la cookie de autenticación
        deleteAuthCookie();
    }

    // Redirigir al home
    header('Location: /home');
    exit;
}


function perfilController() {
    // Si el método es POST, procesamos el cierre de sesión
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        cerrarSesion();
    }

    // Mostrar el perfil del usuario
        mostrarPerfil();
}

perfilController();
