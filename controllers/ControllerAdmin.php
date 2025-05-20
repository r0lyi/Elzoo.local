<?php
// controllers/ControllerAdmin.php

require_once __DIR__ . '/../controllers/ControllerJWT.php';
require_once __DIR__ . '/../controllers/ControllerCookie.php';
require_once __DIR__ . '/../controllers/ControllerTwig.php'; // Asegúrate de que renderView esté aquí

/**
 * Función principal para manejar las vistas del panel de administración.
 * Se encarga de la autenticación y de renderizar el layout principal
 * o solo el componente solicitado si la petición es AJAX.
 *
 * @param string|null $componente_a_mostrar El nombre del componente Twig a cargar (ej. 'usuarioAdmin').
 * Si es null, se muestra el dashboard por defecto (solo si no es AJAX).
 * @param string $welcomeMessage Mensaje informativo opcional (usado en carga inicial).
 */
function adminPanel($componente_a_mostrar = null, $welcomeMessage = '') {
    $jwt = getAuthCookie();
    $isAuthenticated = false;
    $userData = null;

    // Lógica de autenticación JWT
    if ($jwt && verificarJWT($jwt, 'mi_clave_secreta')) {
        $isAuthenticated = true;
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

    // Detectar si la petición es AJAX
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    $templateData = [
        'is_authenticated' => $isAuthenticated,
        'user_data' => $userData,
        'active_component' => $componente_a_mostrar, // Para resaltar la opción activa en el menú
        'content_message' => $welcomeMessage // Mensaje para la carga inicial o para el dashboard
    ];

    // Si es una petición AJAX y se ha especificado un componente, renderiza SOLO el componente.
    if ($isAjax && $componente_a_mostrar) {
        // Renderiza solo el HTML del componente, sin header/footer/sidebar
        // El ControllerTwig debe saber cómo encontrar estos archivos
        renderView('components/' . $componente_a_mostrar . '.html.twig', $templateData);
        exit; // Es crucial salir para que no se renderice el layout completo
    } else {
        // Si no es AJAX (primera carga de la página) o si es /admin sin componente específico,
        // renderiza el layout completo de admin.html.twig.
        renderView('admin.html.twig', $templateData);
        exit;
    }
}