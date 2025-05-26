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
require_once __DIR__ . '/../models/Noticias.php'; // Make sure this is included after ControllerDatabase if Noticias uses it

function home() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $jwt = getAuthCookie();
    $isAuthenticated = false;
    $isAdmin = false;
    $userId = null;

    if ($jwt) {
        if (verificarJWT($jwt, 'mi_clave_secreta')) {
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
    } else {
        session_unset();
        session_destroy();
    }

    // --- Pagination Logic ---
    $itemsPerPage = 12; // Define how many news items per page

    // Get the current page from the URL query parameter 'page'
    // Ensure it's an integer and at least 1
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($currentPage < 1) {
        $currentPage = 1;
    }

    // Get the total count of all news articles
    $totalNewsCount = Noticias::getTotalNoticiasCount();

    // Calculate the total number of pages
    $totalPages = ceil($totalNewsCount / $itemsPerPage);
    if ($totalPages < 1) { // Ensure at least 1 page even if no news
        $totalPages = 1;
    }

    // If the current page is greater than total pages, redirect to the last page
    if ($currentPage > $totalPages && $totalNewsCount > 0) {
        header("Location: ?page=" . $totalPages);
        exit;
    }

    // Calculate the offset for the database query
    $offset = ($currentPage - 1) * $itemsPerPage;

    // Fetch news for the current page using the updated method
    $noticias = Noticias::listNoticias($itemsPerPage, $offset);
    // --- End Pagination Logic ---


    renderView('home.html.twig', [
        'noticias' => $noticias,
        'is_authenticated' => $isAuthenticated,
        'is_admin' => $isAdmin,
        'currentPage' => $currentPage,  // Pass current page to Twig
        'totalPages' => $totalPages     // Pass total pages to Twig
    ]);
    exit;
}

home();