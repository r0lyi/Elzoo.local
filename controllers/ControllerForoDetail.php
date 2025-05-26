<?php
// controllers/ControllerForoDetail.php

require_once __DIR__ . '/ControllerTwig.php';
require_once __DIR__ . '/../models/Foro.php';
require_once __DIR__ . '/../models/ComentarioForo.php'; // Your provided model
require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/ControllerJWT.php';
require_once __DIR__ . '/ControllerCookie.php';


 
function detalleForo(): void
{
    global $secret_key; 

    $isAuthenticated = false;
    $currentUserId = null;
    $message = null; 

    $jwt = getAuthCookie();
    if ($jwt && verificarJWT($jwt, $secret_key)) {
        $isAuthenticated = true;
        $payload = decodificarJWT($jwt, $secret_key);
        if ($payload && isset($payload['sub'])) {
            $currentUserId = $payload['sub'];
        } else {
            error_log("ControllerForoDetail: Valid JWT but 'sub' not found in payload. Deleting cookie.");
            deleteAuthCookie();
            $isAuthenticated = false;
        }
    } else {
        deleteAuthCookie();
    }

   
    $forumId = intval($_GET['id'] ?? 0);

    if ($forumId <= 0) {
        http_response_code(400); // Bad Request
        renderView('400.html.twig', ['mensaje' => 'ID de foro inválido.']);
        exit;
    }


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($isAuthenticated && $currentUserId) {
            $contenidoComentario = trim($_POST['contenido'] ?? ''); // Get comment content from form

            if (empty($contenidoComentario)) {
                $message = ['type' => 'danger', 'text' => 'El comentario no puede estar vacío.'];
            } else {
                $success = ComentarioForo::crear($forumId, $currentUserId, $contenidoComentario);

                if ($success) {
                    
                    header('Location: /foros/' . $forumId . '?message_type=success&message_text=' . urlencode('¡Comentario añadido con éxito!'));
                    exit; // Crucial to stop execution and trigger the redirect
                } else {
                    $message = ['type' => 'danger', 'text' => 'Error al añadir el comentario en la base de datos.'];
                }
            }
        } else {
            $message = ['type' => 'warning', 'text' => 'Debes iniciar sesión para comentar.'];
        }

        
    }

    
    $foro = Foro::obtenerPorId($forumId); 

    if (!$foro) {
        http_response_code(404); // Not Found
        renderView('404.html.twig', ['mensaje' => 'Publicación no encontrada.']);
        exit;
    }

 
    if (isset($_GET['message_type']) && isset($_GET['message_text'])) {
        $message = [
            'type' => htmlspecialchars($_GET['message_type']),
            'text' => htmlspecialchars(urldecode($_GET['message_text']))
        ];
   
    }

    // --- 6. Prepare Forum Data for Twig ---
    $autorForo = Usuarios::obtenerPorId($foro->getAutorId()); // Assuming Usuarios::obtenerPorId returns an array
    $foroData = [
        'id'             => $foro->getId(),
        'titulo'         => $foro->getTitulo(),
        'contenido'      => $foro->getContenido(),
        'fecha_creacion' => $foro->getFechaCreacion(),
        'autor_nombre'   => $autorForo['nombre'] ?? 'Desconocido',
        'autor_id'       => $foro->getAutorId(),
    ];


    $comentarios = $foro->obtenerComentarios(); // Returns array of ComentarioForo objects
    $comentariosData = [];
    foreach ($comentarios as $comentario) {
        $autorComentario = Usuarios::obtenerPorId($comentario->getAutorId());
        $comentariosData[] = [
            'id'             => $comentario->getId(),
            'contenido'      => $comentario->getContenido(),
            'fecha_creacion' => $comentario->getFechaCreacion(),
            'autor_nombre'   => $autorComentario['nombre'] ?? 'Desconocido',
            'autor_id'       => $comentario->getAutorId(),
        ];
    }

    renderView('foro_detalle.html.twig', [
        'foro'             => $foroData,
        'comentarios'      => $comentariosData,
        'is_authenticated' => $isAuthenticated,
        'current_user_id'  => $currentUserId,
        'message'          => $message
    ]);
    exit;
}