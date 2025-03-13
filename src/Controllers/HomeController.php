<?php
namespace Elzoo\Controllers;

use Elzoo\Models\Noticia;
use Elzoo\Core\TwigRenderer;

class HomeController {
    private $twigRenderer;
    private $noticiaModel;

    public function __construct() {
        $this->twigRenderer = new TwigRenderer();
        $this->noticiaModel = new Noticia();
    }

    public function iniciar() {
        // Carga inicial: 10 noticias
        $data = $this->noticiaModel->getNoticiasPaginated(10, 0);

        return $this->twigRenderer->render('home.twig', [
            'noticias' => $data['noticias'],
            'hasMore' => $data['hasMore'],
            'offset' => 10 // Para la próxima carga
        ]);
    }

    public function loadMoreNoticias() {
        try {
            // Validación robusta del offset
            $offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT, [
                'options' => [
                    'default' => 0,
                    'min_range' => 0
                ]
            ]);
            
            // Obtener datos del modelo
            $data = $this->noticiaModel->getNoticiasPaginated(5, $offset);
            
            // Preparar respuesta
            $response = [
                'html' => '',
                'hasMore' => $data['hasMore'],
                'offset' => $offset + 5
            ];
            
            // Generar HTML en el servidor usando Twig
            foreach ($data['noticias'] as $noticia) {
                $response['html'] .= $this->twigRenderer->render('components/noticia_card.twig', [
                    'noticia' => $noticia
                ]);
            }
            
            header('Content-Type: application/json');
            echo json_encode($response);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Error al cargar noticias',
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
}