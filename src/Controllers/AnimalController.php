<?php
namespace Elzoo\Controllers;

use Elzoo\Models\Animal;
use Elzoo\Core\TwigRenderer;

class AnimalController {
    private $animalModel;
    private $twigRenderer;

    public function __construct() {
        $this->twigRenderer = new TwigRenderer();
        $this->animalModel = new Animal();
    }

    /**
     * Método para mostrar la vista con la lista de animales.
     */
    public function listarAnimales() {
        try {
            // Obtener todos los animales de la base de datos
            $animales = $this->animalModel->getAll();

            // Renderizar la vista Twig y pasar los datos de los animales
            return $this->twigRenderer->render('animales.twig', [
                'animales' => $animales,
            ]);
        } catch (\Exception $e) {
            // Manejar errores (puedes redirigir a una página de error o mostrar un mensaje)
            return $this->twigRenderer->render('error.twig', [
                'mensaje' => 'Error al obtener la lista de animales: ' . $e->getMessage(),
            ]);
        }
    }


}