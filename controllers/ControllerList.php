<?php
// Assuming this is your controller file for animals, e.g., controllers/ControllerAnimales.php
// (or wherever your `lista()` function resides)

require_once __DIR__ . '/../models/Animales.php';
require_once __DIR__ . '/../controllers/ControllerJWT.php';
require_once __DIR__ . '/../controllers/ControllerCookie.php';
require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/../controllers/ControllerTwig.php';
require_once __DIR__ . '/../models/Noticias.php'; // Keep if other parts of your app need it


function lista() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $jwt = getAuthCookie();
    $isAuthenticated = false;
    $isAdmin = false; // Initialize isAdmin for consistency, even if not used in this view directly

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
        deleteAuthCookie(); // Ensures invalid/missing token clears cookie
        session_unset();
        session_destroy();
    }

    // --- Pagination Logic for Animals ---
    $itemsPerPage = 12; // Display 7 animals per page

    // Get the current page from the URL query parameter 'page'
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($currentPage < 1) {
        $currentPage = 1;
    }

    // Get the total count of all animals
    $totalAnimalesCount = Animales::getTotalAnimalesCount();

    // Calculate the total number of pages
    $totalPages = ceil($totalAnimalesCount / $itemsPerPage);
    if ($totalPages < 1) { // Ensure at least 1 page even if no animals
        $totalPages = 1;
    }

    // If the current page is greater than total pages, redirect to the last valid page
    if ($currentPage > $totalPages && $totalAnimalesCount > 0) {
        header("Location: ?page=" . $totalPages);
        exit;
    }

    // Calculate the offset for the database query
    $offset = ($currentPage - 1) * $itemsPerPage;

    // Fetch animals for the current page using the updated model method
    $animales = Animales::getAnimales($itemsPerPage, $offset);
    // --- End Pagination Logic ---


    renderView('listaAnimales.html.twig', [ // Assuming your view file is named listaAnimales.html.twig
        'animales' => $animales,
        'is_authenticated' => $isAuthenticated,
        'is_admin' => $isAdmin, // Pass isAdmin if you have any admin-specific features in the view
        'currentPage' => $currentPage,  // Pass current page to Twig
        'totalPages' => $totalPages     // Pass total pages to Twig
    ]);
    exit;
}

lista();