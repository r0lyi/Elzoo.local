<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Elzoo\Controllers\HomeController;
use Elzoo\Routes\Api;

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Configuración de rutas
switch ($requestUri) {
    case '/':
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
    
    default:
        Api::handleApiRequest($requestMethod, $requestUri);
        break;
}