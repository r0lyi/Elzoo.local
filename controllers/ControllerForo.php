<?php
// Controlador para listar todas las publicaciones de foro con comprobación de JWT

require_once __DIR__ . '/ControllerTwig.php';
require_once __DIR__ . '/../models/Foro.php';
require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/ControllerJWT.php';
require_once __DIR__ . '/ControllerCookie.php';

/**
 * Carga todas las publicaciones de foro y las envía a la vista foro.html.twig
 */
function listarForos(): void
{
    // Verificar autenticación a través del JWT en la cookie
    $jwt = getAuthCookie();
    $isAuthenticated = false;
    global $secret_key;
    if ($jwt && verificarJWT($jwt, $secret_key)) {
        $isAuthenticated = true;
    } else {
        deleteAuthCookie();
    }

    // Obtener todas las publicaciones
    $foros = Foro::obtenerTodos();

    // Enriquecer con nombre de autor
    $forosData = [];
    foreach ($foros as $foro) {
        $autor = Usuarios::obtenerPorId($foro->getAutorId());
        $forosData[] = [
            'id'             => $foro->getId(),
            'titulo'         => $foro->getTitulo(),
            'contenido'      => $foro->getContenido(),
            'fecha_creacion' => $foro->getFechaCreacion(),
            'autor_nombre'   => $autor['nombre'] ?? 'Desconocido',
        ];
    }

    // Renderizar la vista de foros
    renderView('foro.html.twig', [
        'foros'             => $forosData,
        'is_authenticated'  => $isAuthenticated
    ]);
    exit;
}
