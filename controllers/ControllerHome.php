<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../models/Noticias.php'; // Ajusta la ruta según donde esté tu archivo Noticias.php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Models\Noticias; // <--- ¡Añade esta línea para importar la clase Noticias desde el namespace Models!

// Función para renderizar vistas con Twig
function renderView(string $template, array $data = []): void {
    $loader = new FilesystemLoader(__DIR__ . '/../views');
    $twig = new Environment($loader);
    echo $twig->render($template, $data);
}

// Verificar si el usuario está autenticado
//session_start();
//$usuario = $_SESSION['usuario'] ?? null; // Suponiendo que el usuario se almacena en la sesión

// Obtener las noticias
$noticias = Noticias::getNoticias();

// Si el usuario está autenticado, mostrar la vista privada
if ($usuario) {
    renderView('homeprivado.html.twig', ['usuario' => $usuario, 'noticias' => $noticias]);
} else {
    // Si no está autenticado, mostrar la vista pública
    renderView('home.html.twig', ['noticias' => $noticias]);
}