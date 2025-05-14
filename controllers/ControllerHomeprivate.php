<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../models/Noticias.php';  // Incluye el modelo Noticias

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Función para renderizar vistas con Twig
function renderView(string $template, array $data = []): void {
    $loader = new FilesystemLoader(__DIR__ . '/../views');
    $twig = new Environment($loader);
    echo $twig->render($template, $data);
}

// Obtiene las noticias
$noticias = Noticias::getNoticias();

// Renderiza la plantilla home.html.twig con las noticias
renderView('homeprivate.html.twig', ['noticias' => $noticias]);