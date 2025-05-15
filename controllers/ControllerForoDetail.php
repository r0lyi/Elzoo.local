<?php
// Controlador para mostrar detalle de una publicación y sus comentarios con JWT

require_once __DIR__ . '/ControllerTwig.php';
require_once __DIR__ . '/../models/Foro.php';
require_once __DIR__ . '/../models/ComentarioForo.php';
require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/ControllerJWT.php';
require_once __DIR__ . '/ControllerCookie.php';

/**
 * Muestra una publicación específica y sus comentarios asociados
 */
function detalleForo(): void
{
    // Verificar autenticación a través del JWT
    $jwt = getAuthCookie();
    $isAuthenticated = false;
    global $secret_key;
    if ($jwt && verificarJWT($jwt, $secret_key)) {
        $isAuthenticated = true;
    } else {
        deleteAuthCookie();
    }

    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        renderView('400.html.twig', ['mensaje' => 'ID de foro inválido']);
        exit;
    }

    $foro = Foro::obtenerPorId($id);
    if (!$foro) {
        http_response_code(404);
        renderView('404.html.twig', ['mensaje' => 'Publicación no encontrada']);
        exit;
    }

    // Obtener autor
    $autor = Usuarios::obtenerPorId($foro->getAutorId());

    // Obtener comentarios
    $comentarios = $foro->obtenerComentarios();
    $comentariosData = [];
    foreach ($comentarios as $comentario) {
        $autorComentario = Usuarios::obtenerPorId($comentario->getAutorId());
        $comentariosData[] = [
            'contenido'      => $comentario->getContenido(),
            'fecha_creacion' => $comentario->getFechaCreacion(),
            'autor_nombre'   => $autorComentario['nombre'] ?? 'Desconocido'
        ];
    }

  renderView('foro_detalle.html.twig', [
    'foro'             => $foroArray,        // array con id, titulo, contenido, autor_nombre, fecha_creacion
    'comentarios'      => $comentariosData,  // array de comentarios
    'is_authenticated' => $isAuthenticated
]);
    exit;
}