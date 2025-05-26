<?php
// controllers/ControllerForoDetail.php

require_once __DIR__ . '/ControllerTwig.php';
require_once __DIR__ . '/../models/Foro.php';
require_once __DIR__ . '/../models/ComentarioForo.php'; // Your provided model
require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/ControllerJWT.php';
require_once __DIR__ . '/ControllerCookie.php';

/**
 * Handles displaying a specific forum post and its comments (GET request),
 * and processing new comment submissions for that post (POST request).
 */
function detalleForo(): void
{
    global $secret_key; // Ensure $secret_key is defined and accessible (e.g., in a config file)

    $isAuthenticated = false;
    $currentUserId = null;
    $message = null; // For success/error messages to pass to Twig

    // --- 1. Authentication Check ---
    $jwt = getAuthCookie();
    if ($jwt && verificarJWT($jwt, $secret_key)) {
        $isAuthenticated = true;
        $payload = decodificarJWT($jwt, $secret_key);
        if ($payload && isset($payload['sub'])) {
            $currentUserId = $payload['sub'];
        } else {
            // Log error if JWT is valid but sub is missing/null (shouldn't happen often)
            error_log("ControllerForoDetail: Valid JWT but 'sub' not found in payload. Deleting cookie.");
            deleteAuthCookie();
            $isAuthenticated = false;
        }
    } else {
        // Clear cookie if no JWT or invalid
        deleteAuthCookie();
    }

    // --- 2. Get Forum ID from $_GET ---
    // The web.php router places the ID from the URL (e.g., /foros/123) into $_GET['id'].
    $forumId = intval($_GET['id'] ?? 0);

    // Basic validation for forum ID
    if ($forumId <= 0) {
        http_response_code(400); // Bad Request
        renderView('400.html.twig', ['mensaje' => 'ID de foro inválido.']);
        exit;
    }

    // --- 3. Handle POST Request (Comment Submission) ---
    // This block runs ONLY if the request method is POST.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($isAuthenticated && $currentUserId) {
            $contenidoComentario = trim($_POST['contenido'] ?? ''); // Get comment content from form

            if (empty($contenidoComentario)) {
                $message = ['type' => 'danger', 'text' => 'El comentario no puede estar vacío.'];
            } else {
                // Use your ComentarioForo model's 'crear' method
                $success = ComentarioForo::crear($forumId, $currentUserId, $contenidoComentario);

                if ($success) {
                    // Success: Redirect back to the same forum detail page with a success message
                    // This prevents form resubmission on refresh and shows the message.
                    header('Location: /foros/' . $forumId . '?message_type=success&message_text=' . urlencode('¡Comentario añadido con éxito!'));
                    exit; // Crucial to stop execution and trigger the redirect
                } else {
                    $message = ['type' => 'danger', 'text' => 'Error al añadir el comentario en la base de datos.'];
                }
            }
        } else {
            $message = ['type' => 'warning', 'text' => 'Debes iniciar sesión para comentar.'];
        }

        // If a message was set due to an error, we don't redirect yet.
        // We will pass the message to the Twig template below for display.
        // If it was a success, we would have already redirected and exited.
    }

    // --- 4. Retrieve Forum Post (for display) ---
    // This runs for both GET requests and if a POST request resulted in an error (no redirect).
    $foro = Foro::obtenerPorId($forumId); // Assumes Foro::obtenerPorId returns a Foro object

    if (!$foro) {
        http_response_code(404); // Not Found
        renderView('404.html.twig', ['mensaje' => 'Publicación no encontrada.']);
        exit;
    }

    // --- 5. Retrieve Message from URL (after a redirect) ---
    // If a redirect happened (e.g., after a successful comment submission),
    // the message will be passed via GET parameters.
    if (isset($_GET['message_type']) && isset($_GET['message_text'])) {
        $message = [
            'type' => htmlspecialchars($_GET['message_type']),
            'text' => htmlspecialchars(urldecode($_GET['message_text']))
        ];
        // Optional: unset GET parameters to prevent message from showing on subsequent page refreshes
        // unset($_GET['message_type']);
        // unset($_GET['message_text']);
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

    // --- 7. Get and Prepare Comments Data for Twig ---
    // The obtenerComentarios() method on the Foro object calls ComentarioForo::obtenerPorForoId().
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

    // --- 8. Render the View ---
    renderView('foro_detalle.html.twig', [
        'foro'             => $foroData,
        'comentarios'      => $comentariosData,
        'is_authenticated' => $isAuthenticated,
        'current_user_id'  => $currentUserId,
        'message'          => $message
    ]);
    exit;
}