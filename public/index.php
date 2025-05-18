<?php
// public/index.php - Punto de entrada principal y despachador de rutas

// Obtener la ruta de la solicitud
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // Usar requestUri para consistencia
$requestMethod = $_SERVER['REQUEST_METHOD']; // Obtener también el método HTTP

// Define rutas base para directorios (ajusta según tu estructura)
// __DIR__ ahora es '.../tu_proyecto/public'
$baseDir = __DIR__ . '/../'; // Ruta a la raíz del proyecto (tu_proyecto/)

$viewDir = $baseDir . 'views/'; // Asumo que views/ está en la raíz, ajusta si no
$controllerDir = $baseDir . 'controllers/'; // controllers/ está en la raíz

// Variable para saber si se encontró y procesó una ruta
$routeHandled = false; // Esta variable será seteada a true dentro de api.php o web.php si se encuentra una ruta

// --- Decidir qué archivo de rutas cargar ---

// Si la solicitud empieza con /api/v1/, cargamos el enrutador API
if (str_starts_with($requestUri, '/api/v1/')) {

    // --- Configuración específica para API ---
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

    // Carga las clases necesarias para la API desde la raíz del proyecto
    // Rutas relativas desde public/index.php
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




    // Eliminar el prefijo /api/v1/ de la ruta para que api.php trabaje con la parte restante
    // Asume que /api/v1/ está justo después de la base del sitio o el subdirectorio público
    $apiUri = substr($requestUri, strlen('/api/v1'));
    $apiUri = trim($apiUri, '/'); // Eliminar la barra inicial/final si queda

    // Incluir el archivo de rutas de la API (está en la raíz del proyecto)
    require_once $baseDir . 'routes/api.php';

} else {
    // Si no, cargamos el enrutador Web
     // Incluir el archivo de rutas Web (está en la raíz del proyecto)
    require_once $baseDir . 'routes/web.php';
}

// --- Manejar 404 si no se encontró ninguna ruta ---
// Después de incluir el archivo de rutas y ejecutar su lógica,
// verificamos si la variable $routeHandled se puso a true.
if (!$routeHandled) {
    http_response_code(404);
    // Asegúrate de que la ruta a tu plantilla 404 sea correcta desde public/index.php
    // Si 404.html.twig está en views/ (en la raíz)
    $notFoundPage = $viewDir . '404.html'; // Ajusta la extensión o usa tu motor de plantillas
    if (file_exists($notFoundPage)) {
       // Si usas Twig, renderizarías la plantilla aquí
       // require_once $notFoundPage; // Esto incluiría un archivo PHP/HTML plano
       echo "<h1>404 - Not Found</h1><p>The requested resource could not be found.</p>"; // Mensaje simple de fallback
    } else {
        echo "404 Not Found"; // Mensaje de fallback final
    }
}

?>