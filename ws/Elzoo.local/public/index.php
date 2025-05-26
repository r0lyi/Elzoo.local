<?php
use Controllers\ControllerHome;

// Lógica de enrutamiento
$request = $_SERVER['REQUEST_URI'];
$viewDir = __DIR__ . '/../views/';
$controllerDir = __DIR__ . '/../controllers/';//$controlador = '/controller/' ;

switch ($request) {
    case '':
    case '/':   
        case '/home':
            ControllerHome::render();
            break;
        
    /*case '/species_list':
        require __DIR__ . $controller . 'ControllerList.php';
        break;
    case '/register':
        require __DIR__ . $controller . 'ControllerRegister.php';
        break;
    case '/login':
        require __DIR__ . $controller . 'ControllerLogin.php';
        break;
     case '/perfil':

    case '/homeprivate':
        require __DIR__ . $controller . 'ControllerHomeprivate.php';
        break;




        require __DIR__ . $viewDir . 'perfil.php';
        break;
    case '/adopcion':
        require __DIR__ . $viewDir . 'adopcion.php';
        break;
    
   
    case '/admin':
        require __DIR__ . $viewDir . 'admin.php';
        break;
    case '/forum':
        require __DIR__ . $viewDir . 'forum.php';
        break;*/
    //default:
        //http_response_code(404);
        //require __DIR__ . $viewDir . '404.php.twig';
}
?>

