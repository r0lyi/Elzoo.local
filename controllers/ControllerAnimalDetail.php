<?php
// Controlador para mostrar un animal según su slug (nombre)

require_once __DIR__ . '/ControllerTwig.php';
require_once __DIR__ . '/../models/Animales.php';


function mostrarAnimal(): void
{
    // Ejemplo de URL: /animales/leon-marino
    $slug = $_GET['nombre'] ?? '';
    if (!$slug) {
        http_response_code(400);
        renderView('400.html.twig', ['mensaje' => 'Solicitud inválida']);
        exit;
    }

    $animal = Animales::getPorNombre($slug);
    if (!$animal) {
        http_response_code(404);
        renderView('404.html.twig', ['mensaje' => 'Animal no encontrado']);
        exit;
    }

    renderView('animal_detalle.html.twig', [
        'animal' => $animal
    ]);
    exit;
}