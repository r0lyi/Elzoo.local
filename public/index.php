<?php
// public/index.php - Punto de entrada principal y despachador de rutas

// Obtener la ruta de la solicitud
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // Ahora se llama $request
$requestMethod = $_SERVER['REQUEST_METHOD']; // Obtener también el método HTTP


$baseDir = __DIR__ . '/../'; 

$viewDir = $baseDir . 'views/'; 
$controllerDir = $baseDir . 'controllers/'; // controllers/ está en la raíz

$routeHandled = false; 


// Si la solicitud empieza con /api/v1/, cargamos el enrutador API
if (str_starts_with($request, '/api/v1/')) {

    header("Access-Control-Allow-Origin: *"); // Permite peticiones desde cualquier origen (CORS) - Ajusta para producción
    header("Content-Type: application/json; charset=UTF-8"); // Indicar que la respuesta es JSON
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    // Responde a las solicitudes preflight de CORS
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    require_once $baseDir . 'models/Usuarios.php';
    require_once $baseDir . 'models/Foro.php'; 
    require_once $baseDir . 'models/ComentarioForo.php';
    require_once $baseDir . 'models/Animales.php'; 
    require_once $baseDir . 'models/Noticias.php'; 


    require_once $baseDir . 'controllers/ControllerDatabase.php';
    require_once $baseDir . 'controllers/http/UsuariosController.php';
    require_once $baseDir . 'controllers/http/ComentarioForoController.php';
    require_once $baseDir . 'controllers/http/ForoController.php'; 
    require_once $baseDir . 'controllers/http/AnimalesController.php'; 
    require_once $baseDir . 'controllers/http/NoticiasController.php'; 





    $apiUri = substr($$request, strlen('/api/v1'));
    $apiUri = trim($apiUri, '/'); // Eliminar la barra inicial/final si queda

    require_once $baseDir . 'routes/api.php';

} else {

    require_once $baseDir . 'routes/web.php';
}


if (!$routeHandled) {
   // http_response_code(404);

    $notFoundPage = $viewDir . '404.html'; // Ajusta la extensión o usa tu motor de plantillas
    if (file_exists($notFoundPage)) {

       echo "<h1>404 - Not Found</h1><p>The requested resource could not be found.</p>"; // Mensaje simple de fallback
    } else {
        echo "404 Not Found"; // Mensaje de fallback final
    }
}

?>