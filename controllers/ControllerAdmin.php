<?php
// controllers/ControllerAdmin.php

// Cargamos solo lo necesario para autenticación y renderizado de vistas
require_once __DIR__ . '/../controllers/ControllerJWT.php';
require_once __DIR__ . '/../controllers/ControllerCookie.php';
require_once __DIR__ . '/../controllers/ControllerTwig.php'; // Asume que renderView está aquí

/**
 * Función principal para manejar las vistas del panel de administración.
 * Se encarga de la autenticación y de renderizar el layout principal
 * incluyendo el componente solicitado, mostrando un mensaje simple.
 *
 * @param string|null $componente_a_mostrar El nombre del componente Twig a "activar" (ej. 'usuarioAdmin').
 * Si es null, se muestra el dashboard por defecto.
 * @param string $mensaje_contenido Un mensaje simple a mostrar en el área de contenido.
 */
function adminPanel($componente_a_mostrar = null, $mensaje_contenido = '') {
    $jwt = getAuthCookie();
    $isAuthenticated = false;
    $userData = null;

    // Lógica de autenticación JWT
    if ($jwt && verificarJWT($jwt, 'mi_clave_secreta')) {
        $isAuthenticated = true;
        // Opcional: Decodificar el JWT para obtener datos del usuario
        $payload = json_decode(base64_decode(explode('.', $jwt)[1]), true);
        $userData = $payload;
    } else {
        deleteAuthCookie(); // Elimina la cookie si el token es inválido
        header('Location: /login'); // Redirige al login si no está autenticado
        exit;
    }

    // Si el usuario no está autenticado, no debería ver el panel de administración
    if (!$isAuthenticated) {
        echo "Acceso denegado. Por favor, inicia sesión.";
        exit;
    }

    $templateData = [
        'is_authenticated' => $isAuthenticated,
        'user_data' => $userData,
        'active_component' => $componente_a_mostrar, // Para resaltar la opción activa en el menú
        'content_message' => $mensaje_contenido // Pasa el mensaje al área de contenido
    ];

    // Siempre renderizamos el layout principal de admin.
    renderView('admin.html.twig', $templateData);
    exit;
}