<?php

namespace Controllers;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../models/Noticias.php';

use Models\Noticias;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ControllerHome
{
    public static function render(): void
    {
        session_start();
        $usuario = $_SESSION['usuario'] ?? null;

        // Obtener noticias
        $noticias = Noticias::getNoticias();

        // Configurar Twig
        $loader = new FilesystemLoader(__DIR__ . '/../views');
        $twig = new Environment($loader);

        // Renderizar según sesión
        if ($usuario) {
            echo $twig->render('homeprivado.html.twig', ['usuario' => $usuario, 'noticias' => $noticias]);
        } else {
            echo $twig->render('home.html.twig', ['noticias' => $noticias]);
        }
    }
}
