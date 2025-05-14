<?php

$request = $_SERVER['REQUEST_URI'];

// Define rutas relativas al nivel superior
$viewDir = __DIR__ . '/../views/';
$controllerDir = __DIR__ . '/../controllers/';

switch ($request) {
    case '':
    case '/':
    case '/home':
        require_once $controllerDir . 'ControllerHome.php';
        break;

    // Puedes descomentar y adaptar según tus necesidades
    /*
    case '/species_list':
        require_once $controllerDir . 'ControllerList.php';
        break;

    case '/register':
        require_once $controllerDir . 'ControllerRegister.php';
        break;

    case '/login':
        require_once $controllerDir . 'ControllerLogin.php';
        break;

    case '/perfil':
        require_once $viewDir . 'perfil.php';
        break;

    case '/homeprivate':
        require_once $controllerDir . 'ControllerHomeprivate.php';
        break;

    case '/adopcion':
        require_once $viewDir . 'adopcion.php';
        break;

    case '/admin':
        require_once $viewDir . 'admin.php';
        break;

    case '/forum':
        require_once $viewDir . 'forum.php';
        break;

    default:
        http_response_code(404);
        require_once $viewDir . '404.php.twig';
        break;
    */
}
