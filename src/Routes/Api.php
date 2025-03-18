<?php
namespace Elzoo\Routes;

use Elzoo\Controllers\NoticiaController;
use Elzoo\Core\Response;

class Api {
    public static function handleApiRequest($method, $uri) {
        // Rutas de la API
        $apiRoutes = [
            '/api/noticias' => [
                'GET' => function () {
                    $controller = new NoticiaController();
                    $controller->getAll();
                },
                'POST' => function () {
                    $controller = new NoticiaController();
                    $controller->create();
                },
            ],
            '/api/noticias/{id}' => [
                'GET' => function ($id) {
                    $controller = new NoticiaController();
                    $controller->getById($id);
                },
                'PUT' => function ($id) {
                    $controller = new NoticiaController();
                    $controller->update($id);
                },
                'DELETE' => function ($id) {
                    $controller = new NoticiaController();
                    $controller->delete($id);
                },
            ],
        ];

        // Manejo de rutas API
        foreach ($apiRoutes as $route => $methods) {
            if (preg_match('/^\/api\/noticias(?:\/(\d+))?$/', $uri, $matches)) {
                $id = $matches[1] ?? null;

                if (array_key_exists($method, $methods)) {
                    $methods[$method]($id);
                    return;
                } else {
                    Response::error('Método no permitido', 405);
                    return;
                }
            }
        }

        // Si no se encuentra la ruta
        Response::error('Ruta no encontrada', 404);
    }
}