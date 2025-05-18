<?php

switch (true) {

    case $request === '' || $request === '/' || $request === '/home':
        require_once $controllerDir . 'ControllerHome.php';
        $routeHandled = true; // ¡Importante! Indica que una ruta fue encontrada y manejada
        break;

    case $request === '/register':
        require_once $controllerDir . 'ControllerRegister.php';
        $routeHandled = true;
        break;

    case $request === '/login':
        require_once $controllerDir . 'ControllerLogin.php';
        $routeHandled = true;
        break;

    case $request === '/perfil':
        require_once $controllerDir . 'ControllerPerfil.php';
        $routeHandled = true;
        break;

    case $request === '/animales':
        require_once $controllerDir . 'ControllerList.php';
        $routeHandled = true;
        break;

    case $request === '/admin':
        require_once $controllerDir . 'ControllerAdmin.php';
        $routeHandled = true;
        break;

    // Ruta foro: listado (GET /forum)
    case $request === '/forum':
        require_once $controllerDir . 'ControllerForo.php';
        listarForos();
        $routeHandled = true;
        break;

    // Ruta foro: detalle (GET /forum/{id})
    case preg_match('#^/forum/(\d+)$#', $request, $m_foro) === 1 && $_SERVER['REQUEST_METHOD'] === 'GET':
        $_GET['id'] = $m_foro[1];
        require_once $controllerDir . 'ControllerForoDetail.php';
        detalleForo();
        $routeHandled = true;
        break;

    case $request === '/adopcion':
        require_once $controllerDir . 'ControllerAdopcion.php';
        $routeHandled = true;
        break;

    // Ruta dinámica para detalle de animal: /animales/{slug}
    case preg_match('#^/animales/([^/]+)$#', $request, $matches) === 1:
        $_GET['nombre'] = $matches[1];
        require_once $controllerDir . 'ControllerAnimalDetail.php';
        mostrarAnimal();
        $routeHandled = true;
        break;

    // IMPORTANTE: No incluyas un 'default:' aquí para el 404.
    // Si ningún caso web coincide, $routeHandled seguirá siendo false,
    // y el 404 se manejará en index.php.
}