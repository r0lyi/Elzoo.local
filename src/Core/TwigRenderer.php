<?php
namespace Elzoo\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigRenderer {
    private $twig;

    public function __construct() {
        $loader = new FilesystemLoader(__DIR__ . '/../../views');
        $this->twig = new Environment($loader);
    }

    public function render($template, array $data = []) {
        return $this->twig->render($template, $data);
    }
}