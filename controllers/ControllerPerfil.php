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

    // Si el JWT o los datos son inválidos, redirigir al home (or handle specifically for AJAX)
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

    // Obtener la información del usuario desde la base de datos usando su ID
    $usuario = Usuarios::obtenerPorId($datos['sub']);

    // Si no se encuentra el usuario, redirigir al home (or handle specifically for AJAX)
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

    // Pass 'isAjaxRequest' to the Twig renderer if it can use it for different layouts
    // However, in this setup, 'perfil.html.twig' will always be rendered,
    // and the JavaScript will extract the needed part.
    renderView('perfil.html.twig', ['usuario' => $usuario]);
    // The Twig rendering happens. The crucial part is that the JS then extracts only the #profile-card-content
    // from the full HTML response.
}

function cerrarSesion() {
    // ... (Your existing cerrarSesion function remains unchanged) ...
    // Obtener el JWT desde la cookie
    $jwt = getAuthCookie();

    // Si el JWT existe, eliminar el token de la base de datos y la cookie
    if ($jwt) {
        $datos = decodificarJWT($jwt);

        if ($datos && isset($datos['sub'])) {
            // Eliminar el token de la base de datos
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