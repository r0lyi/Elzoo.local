<?php
require_once __DIR__ . '/../controllers/ControllerJWT.php';
require_once __DIR__ . '/../controllers/ControllerCookie.php';
require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/../controllers/ControllerTwig.php';
require_once __DIR__ . '/../models/Noticias.php';


function adopcion() {
    $jwt = getAuthCookie();
    $noticias = Noticias::getNoticias();
    $isAuthenticated = false;

    if ($jwt && verificarJWT($jwt, 'mi_clave_secreta')) {
        $isAuthenticated = true;
    } else {
        deleteAuthCookie(); // Elimina la cookie si el token es inválido
    }

    renderView('adopcion.html.twig', [
        'noticias' => $noticias,
        'is_authenticated' => $isAuthenticated
    ]);
    exit;
}
adopcion();