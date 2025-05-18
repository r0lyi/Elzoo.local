<?php
// controllers/animal_controller.php

require_once __DIR__ . '/../controllers/TwigController.php';
require_once __DIR__ . '/../models/Animales.php';

// Obtener el slug del nombre desde la ruta o query string
$slug = $_GET['nombre'] ?? '';

// Buscar el animal por nombre (slug)
$animal = Animales::getPorNombre($slug);

if (!$animal) {
    http_response_code(404);
    // Renderizar una vista de error o página 404
    renderView('404.html.twig', [
        'mensaje' => 'Animal no encontrado',
    ]);
    exit;
}

// Renderizar la plantilla de detalle con el objeto Animales
renderView('animal_detalle.html.twig', [
    'animal' => $animal,
]);