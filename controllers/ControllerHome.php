<?php
// controllers/ControllerHome.php

// Orden de inclusión de archivos:
// 1. ControllerCookie.php (para getAuthCookie y deleteAuthCookie)
// 2. ControllerJWT.php (para verificarJWT y decodificarJWT)
// 3. Usuarios.php (para Usuarios::esAdmin)
// 4. ControllerTwig.php (para renderView)
// 5. Noticias.php
require_once __DIR__ . '/../controllers/ControllerCookie.php';
require_once __DIR__ . '/../controllers/ControllerJWT.php';
require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/../controllers/ControllerTwig.php';
require_once __DIR__ . '/../models/Noticias.php';


function home() {
    // Es buena práctica iniciar la sesión si la usas en algún momento.
    // Aunque para este caso específico de 'is_admin' basado en JWT, la sesión no es estrictamente necesaria
    // para el menú, puede serlo para otras lógicas de tu aplicación.
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $jwt = getAuthCookie(); // Obtiene el token de la cookie
    $noticias = Noticias::listNoticias();
    $isAuthenticated = false; // Por defecto, no autenticado
    $isAdmin = false;         // Por defecto, no es admin
    $userId = null;           // Para almacenar el ID del usuario del JWT

    if ($jwt) { // Si hay un JWT en la cookie
        if (verificarJWT($jwt, 'mi_clave_secreta')) { // Si el JWT es válido
            $isAuthenticated = true;
            $payload = decodificarJWT($jwt); // Decodifica el JWT para obtener su contenido

            // Comprueba si el payload existe y contiene 'user_id'
            if ($payload && isset($payload['sub'])) {
                $userId = $payload['sub'];
                // Llama al método del modelo Usuarios para saber si es admin
                $isAdmin = Usuarios::esAdmin($userId);

                // Opcional: Almacenar en sesión (útil para que otros controladores PHP no tengan que decodificar el JWT de nuevo)
                $_SESSION['sub'] = $userId;
                $_SESSION['user_role'] = $isAdmin ? 'admin' : 'user'; // Guarda el rol real
            } else {
                // Si el JWT es válido pero no tiene user_id, se considera inválido para fines prácticos.
                $isAuthenticated = false;
                deleteAuthCookie(); // Elimina la cookie (posiblemente un JWT mal formado)
                session_unset();
                session_destroy();
            }
        } else {
            // El JWT existe pero es inválido (ej. expirado, firma incorrecta)
            deleteAuthCookie(); // Elimina la cookie inválida
            session_unset();
            session_destroy();
        }
    } else {
        // No hay JWT en la cookie, por lo tanto, no autenticado.
        // Asegúrate de que no haya restos de sesión de un intento anterior
        session_unset();
        session_destroy();
    }

    renderView('home.html.twig', [
        'noticias' => $noticias,
        'is_authenticated' => $isAuthenticated, // Pasa true si el usuario está logueado
        'is_admin' => $isAdmin                // Pasa true si el usuario es admin
    ]);
    exit;
}

home();