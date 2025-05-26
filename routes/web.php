<?php

// Assuming $controllerDir is defined somewhere before this code block
// For example: $controllerDir = __DIR__ . '/controllers/';

$request = $_SERVER['REQUEST_URI'];
$request = strtok($request, '?'); // Remove query string (e.g., ?message_type=...)

$routeHandled = false; // Initialize the flag

switch (true) { // Use true to evaluate each case condition
    // --- Existing Static Routes ---
    case $request === '' || $request === '/' || $request === '/home':
        require_once $controllerDir . 'ControllerHome.php';
        home(); // Call the home function
        $routeHandled = true;
        break;

    case $request === '/register':
        require_once $controllerDir . 'ControllerRegister.php';
        handleRegister(); // Call the register function
        $routeHandled = true;
        break;

    case $request === '/login':
        require_once $controllerDir . 'ControllerLogin.php';
        handleLogin(); // Call the login function
        $routeHandled = true;
        break;

    case $request === '/perfil':
        require_once $controllerDir . 'ControllerPerfil.php';
        handlePerfil(); // Call the perfil function
        $routeHandled = true;
        break;

    case $request === '/animales':
        require_once $controllerDir . 'ControllerList.php';
        listarAnimales(); // Assuming this is the correct function
        $routeHandled = true;
        break;

    case $request === '/admin':
        require_once $controllerDir . 'ControllerAdmin.php';
        admin(); // Assuming this function handles the default /admin
        $routeHandled = true;
        break;

    // Rutas para sub-secciones del panel de administración (Dynamic Admin Routes)
    case preg_match('#^/admin/([^/]+)$#', $request, $matches_admin_sub) === 1:
        $adminSection = $matches_admin_sub[1];
        require_once $controllerDir . 'ControllerAdmin.php';
        admin($adminSection);
        $routeHandled = true;
        break;

    // Ruta para el listado de foros y creación de nuevos posts (your existing /foros route)
    case $request === '/foros':
        require_once $controllerDir . 'ControllerForo.php';
        listarForos(); // This controller should handle GET for list and POST for creating a forum
        $routeHandled = true;
        break;

    // --- NEW: Dynamic Route for Forum Detail & Comment Handling ---
    // This case will match URLs like /foros/1, /foros/25, etc.
    // It captures the ID and directs both GET and POST requests to ControllerForoDetail.
    case preg_match('#^/foros/(\d+)$#', $request, $matches_foro_detail) === 1:
        $_GET['id'] = $matches_foro_detail[1]; // Extract the ID and put it in $_GET
        require_once $controllerDir . 'ControllerForoDetail.php';
        detalleForo(); // This controller will handle both GET (display) and POST (comment creation)
        $routeHandled = true;
        break;

    // Ruta dinámica para detalle de animal: /animales/{slug}
    case preg_match('#^/animales/([^/]+)$#', $request, $matches_animal_detail) === 1:
        $_GET['nombre'] = $matches_animal_detail[1];
        require_once $controllerDir . 'ControllerAnimalDetail.php';
        mostrarAnimal();
        $routeHandled = true;
        break;

    // IMPORTANT: No default case in this switch.
    // The 404 handling should be outside the switch, after all case statements.
}

// --- Static Files Handling (This part should be in your main index.php or similar) ---
if (!$routeHandled) {
    $filePath = __DIR__ . '/public' . $request; // Adjust path as needed
    if (file_exists($filePath) && !is_dir($filePath)) {
        $mimeType = mime_content_type($filePath);
        header('Content-Type: ' . $mimeType);
        readfile($filePath);
        exit; // Exit after serving static file
    }
}

// --- 404 Not Found Handling (This part should be in your main index.php or similar) ---
if (!$routeHandled) {
    http_response_code(404);
    echo "Página no encontrada";
    // Optionally render a Twig 404 page here:
    // require_once $controllerDir . 'ControllerTwig.php';
    // renderView('404.html.twig', ['mensaje' => 'Página no encontrada']);
    exit;
}

?>