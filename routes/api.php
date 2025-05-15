<?php

switch (true) {

    // Ejemplo de ruta API estática
    case $request === '/api/v1/users' && $_SERVER['REQUEST_METHOD'] === 'GET':
        // require_once $controllerDir . 'ApiControllerUsers.php'; // Carga tu controlador API
        echo json_encode(['message' => 'API Users List']); // Ejemplo de respuesta API
        $routeHandled = true; // ¡Importante! Indica que una ruta fue encontrada y manejada
        break;

    // Ejemplo de ruta API dinámica
    case preg_match('#^/api/v1/products/(\d+)$#', $request, $matches) === 1 && $_SERVER['REQUEST_METHOD'] === 'GET':
        $_GET['id'] = $matches[1];
        // require_once $controllerDir . 'ApiControllerProducts.php'; // Carga tu controlador API
        echo json_encode(['message' => 'API Product Detail', 'product_id' => $matches[1]]); // Ejemplo de respuesta API
        $routeHandled = true;
        break;


}