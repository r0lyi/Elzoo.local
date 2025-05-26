<?php
// Controlador para listar y crear publicaciones de foro con comprobación de JWT

require_once __DIR__ . '/ControllerTwig.php';
require_once __DIR__ . '/../models/Foro.php';
require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/ControllerJWT.php';
require_once __DIR__ . '/ControllerCookie.php';

/**
 * Carga todas las publicaciones de foro y las envía a la vista foro.html.twig
 * También maneja la creación de nuevas publicaciones si la solicitud es POST.
 */
function listarForos(): void
{
    global $secret_key; // Asegúrate de que $secret_key esté definida en un archivo de configuración incluido.

    $isAuthenticated = false;
    $currentUserId = null;
    $message = null; // Para mensajes de éxito/error

    // 1. Verificar autenticación a través del JWT en la cookie
    $jwt = getAuthCookie();
    if ($jwt && verificarJWT($jwt, $secret_key)) {
        $isAuthenticated = true;
        // Si el JWT es válido, decodifícalo para obtener el ID del usuario
        $payload = decodificarJWT($jwt, $secret_key);
        if ($payload && isset($payload['sub'])) {
            $currentUserId = $payload['sub'];
        } else {
            // JWT válido, pero sin user_id o payload nulo, algo no va bien.
            error_log("JWT válido, pero 'sub' no encontrado en el payload o payload nulo.");
            deleteAuthCookie(); // Invalidar la sesión si el ID no se puede obtener
            $isAuthenticated = false; // Asegurarse de que el estado es no autenticado
        }
    } else {
        // Si no hay JWT o no es válido, eliminar la cookie.
        deleteAuthCookie();
    }

    // 2. Manejar la solicitud POST para crear un nuevo foro
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($isAuthenticated && $currentUserId) { // Ambas condiciones deben ser verdaderas
            $titulo = trim($_POST['titulo'] ?? '');
            $contenido = trim($_POST['contenido'] ?? '');

            if (empty($titulo) || empty($contenido)) {
                $message = ['type' => 'danger', 'text' => 'El título y el contenido no pueden estar vacíos.'];
            } else {
                $exito = Foro::crear($titulo, $contenido, $currentUserId);

                if ($exito) {
                    // Redirigir para evitar el reenvío del formulario y mostrar el mensaje
                    header('Location: /foros?message_type=success&message_text=' . urlencode('¡Publicación creada con éxito!'));
                    exit;
                } else {
                    $message = ['type' => 'danger', 'text' => 'Error al crear la publicación en la base de datos.'];
                }
            }
        } else {
            // Este es el mensaje que aparece si el usuario no está autenticado o no se pudo obtener su ID
            $message = ['type' => 'warning', 'text' => 'Debes iniciar sesión para crear publicaciones.'];
        }
    }

    // Si hubo una redirección desde POST (después de crear un foro), recuperar el mensaje
    if (isset($_GET['message_type']) && isset($_GET['message_text'])) {
        $message = [
            'type' => htmlspecialchars($_GET['message_type']),
            'text' => htmlspecialchars(urldecode($_GET['message_text']))
        ];
    }


    // 3. Obtener todas las publicaciones para mostrarlas
    $foros = Foro::obtenerTodos();

    // 4. Enriquecer con nombre de autor
    $forosData = [];
    foreach ($foros as $foro) {
        $autor = Usuarios::obtenerPorId($foro->getAutorId());
        $forosData[] = [
            'id'             => $foro->getId(),
            'titulo'         => $foro->getTitulo(),
            'contenido'      => $foro->getContenido(),
            'fecha_creacion' => $foro->getFechaCreacion(),
            'autor_nombre'   => $autor['nombre'] ?? 'Desconocido',
            'autor_id'       => $foro->getAutorId(),
        ];
    }

    // 5. Renderizar la vista de foros
    renderView('foro.html.twig', [
        'foros'             => $forosData,
        'is_authenticated'  => $isAuthenticated,
        'current_user_id'   => $currentUserId,
        'message'           => $message
    ]);
    exit;
}