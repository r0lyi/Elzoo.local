<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Elzoo\Controllers\RegisterController;
use Elzoo\Controllers\HomeController;
use Elzoo\Controllers\AnimalController;
use Elzoo\Controllers\LoginController;
use Elzoo\Routes\Api;

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Configuración de rutas
switch ($requestUri) {
    case '/':
    case '/home':
        if ($requestMethod === 'GET') {

            $controller = new HomeController();
            echo $controller->iniciar();
        }
        break;
    
    case '/load-more-noticias':
        if ($requestMethod === 'GET') {

            $controller = new HomeController();
            $controller->loadMoreNoticias();
        }
        break;
    case '/animales':
        if ($requestMethod === 'GET') {

            $controller = new AnimalController();
            echo $controller->listarAnimales();
        }
        break;
        case '/register':
            if ($requestMethod === 'GET') {
                $controller = new RegisterController();
                $controller->register();
            } 
    case '/login':
            if ($requestMethod === 'GET') {
                $controller = new LoginController();
                $controller->login();
            }
    break;    
        

    
    default:
        Api::handleApiRequest($requestMethod, $requestUri);
        break;
}