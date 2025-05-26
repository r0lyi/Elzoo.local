<?php


require_once __DIR__ . '/../models/Animales.php';
require_once __DIR__ . '/../controllers/ControllerJWT.php';
require_once __DIR__ . '/../controllers/ControllerCookie.php';
require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/../controllers/ControllerTwig.php';
require_once __DIR__ . '/../models/Noticias.php'; 


function lista() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $jwt = getAuthCookie();
    $isAuthenticated = false;
    $isAdmin = false; 

    if ($jwt && verificarJWT($jwt, 'mi_clave_secreta')) {
        $isAuthenticated = true;
        $payload = decodificarJWT($jwt);
        if ($payload && isset($payload['sub'])) {
            $userId = $payload['sub'];
            $isAdmin = Usuarios::esAdmin($userId);
            $_SESSION['sub'] = $userId;
            $_SESSION['user_role'] = $isAdmin ? 'admin' : 'user';
        } else {
            $isAuthenticated = false;
            deleteAuthCookie();
            session_unset();
            session_destroy();
        }
    } else {
        deleteAuthCookie(); 
        session_unset();
        session_destroy();
    }

    $itemsPerPage = 12; 

    // Get the current page from the URL query parameter 'page'
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($currentPage < 1) {
        $currentPage = 1;
    }

    // Get the total count of all animals
    $totalAnimalesCount = Animales::getTotalAnimalesCount();

    $totalPages = ceil($totalAnimalesCount / $itemsPerPage);
    if ($totalPages < 1) { 
        $totalPages = 1;
    }

    if ($currentPage > $totalPages && $totalAnimalesCount > 0) {
        header("Location: ?page=" . $totalPages);
        exit;
    }

    $offset = ($currentPage - 1) * $itemsPerPage;

    $animales = Animales::getAnimales($itemsPerPage, $offset);


    renderView('listaAnimales.html.twig', [ 
        'animales' => $animales,
        'is_authenticated' => $isAuthenticated,
        'is_admin' => $isAdmin, 
        'currentPage' => $currentPage,  
        'totalPages' => $totalPages     
    ]);
    exit;
}

lista();