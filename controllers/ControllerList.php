
<?php
require_once __DIR__ . '/../models/Animales.php';  // Incluye el modelo Noticias

require_once __DIR__ . '/../controllers/ControllerJWT.php';
require_once __DIR__ . '/../controllers/ControllerCookie.php';
require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/../controllers/ControllerTwig.php';
require_once __DIR__ . '/../models/Noticias.php';


function lista() {
    $jwt = getAuthCookie();
    $animales = Animales::getAnimales();
    $isAuthenticated = false;

    if ($jwt && verificarJWT($jwt, 'mi_clave_secreta')) {
        $isAuthenticated = true;
    } else {
        deleteAuthCookie(); // Elimina la cookie si el token es inválido
    }

    renderView('listaAnimales.html.twig', [
        'animales' => $animales,
        'is_authenticated' => $isAuthenticated
    ]);
    exit;
}
lista();