<?php
// index.php - Punto de entrada principal y despachador de rutas

// Obtener la ruta de la solicitud
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Define rutas base para directorios (ajusta según tu estructura)
// Esto asume que index.php está en la raíz pública y views/controllers están fuera de ella.
$viewDir = __DIR__ . '/../views/';      // Esta ruta parece correcta si views está fuera de public
$controllerDir = __DIR__ . '/../controllers/'; // Esta ruta parece correcta si controllers está fuera de public

// Variable para saber si se encontró y procesó una ruta
$routeHandled = false;

// --- Decidir qué archivo de rutas cargar ---

// Si la solicitud empieza con /api/v1/, cargamos el enrutador API
if (str_starts_with($request, '/api/v1/')) {
    // ¡CORREGIDO! La ruta sube un nivel para encontrar la carpeta routes
    require_once __DIR__ . '/../routes/api.php';
} else {
    // Si no, cargamos el enrutador Web
    // ¡CORREGIDO! La ruta sube un nivel para encontrar la carpeta routes
    require_once __DIR__ . '/../routes/web.php';
}

// --- Manejar 404 si no se encontró ninguna ruta ---
// Después de incluir el archivo de rutas y ejecutar su switch,
// verificamos si la variable $routeHandled se puso a true.
if (!$routeHandled) {
    http_response_code(404);
    // Asegúrate de que la ruta a tu plantilla 404 sea correcta
    $notFoundPage = $viewDir . '404.html.twig'; // O tu archivo 404
    if (file_exists($notFoundPage)) {
        require_once $notFoundPage;
    } else {
        echo "404 Not Found"; // Mensaje de fallback
    }
}

?>