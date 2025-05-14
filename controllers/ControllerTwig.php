<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

function renderView(string $template, array $data = []): void {
    $loader = new FilesystemLoader(__DIR__ . '/../views');
    $twig = new Environment($loader);
   
    echo $twig->render($template, $data);
}


