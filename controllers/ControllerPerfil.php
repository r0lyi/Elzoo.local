<?php

require_once __DIR__ . '/../controllers/ControllerJWT.php';
require_once __DIR__ . '/../controllers/ControllerCookie.php';
require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/../controllers/ControllerTwig.php'; // Make sure this path is correct

function mostrarPerfil($isAjaxRequest = false) { // Added a parameter
    // Obtener el JWT desde la cookie
    $jwt = getAuthCookie();

    // Decodificar el JWT y obtener los datos del usuario
    $datos = decodificarJWT($jwt);

    if (!$jwt || !$datos || !isset($datos['sub'])) {
        if ($isAjaxRequest) {
            http_response_code(401); // Unauthorized
            echo '<p class="text-center text-danger p-4">No estás autenticado.</p>';
            exit;
        } else {
            header('Location: /home');
            exit;
        }
    }

    $usuario = Usuarios::obtenerPorId($datos['sub']);

    if (!$usuario) {
        if ($isAjaxRequest) {
            http_response_code(404); // Not Found
            echo '<p class="text-center text-danger p-4">Usuario no encontrado.</p>';
            exit;
        } else {
            header('Location: /home');
            exit;
        }
    }
   
    renderView('perfil.html.twig', ['usuario' => $usuario]);
    
}

function cerrarSesion() {
    
    $jwt = getAuthCookie();

    if ($jwt) {
        $datos = decodificarJWT($jwt);

        if ($datos && isset($datos['sub'])) {
            Usuarios::eliminarTokenPorId($datos['sub']);
        }

        // Eliminar la cookie de autenticación
        deleteAuthCookie();
    }

    // Redirigir al home
    header('Location: /home');
    exit;
}


function perfilController() {
    // Detect if it's an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

    // Si el método es POST, procesamos el cierre de sesión
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        cerrarSesion();
    } else { // Handle GET requests
        mostrarPerfil($isAjax); // Pass the AJAX detection
    }
}

perfilController();