<?php

$requestUriFromApiScope = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (str_starts_with($requestUriFromApiScope, '/api/v1/')) {
    $apiUri = substr($requestUriFromApiScope, strlen('/api/v1'));
    $apiUri = trim($apiUri, '/');
} else {
    // Safeguard: Should not happen if index.php logic is correct
    error_log("ERROR: routes/api.php included, but request URI does not start with /api/v1/: " . $requestUriFromApiScope);
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Internal API routing error."]);
    exit();
}

// Divide $apiUri en segmentos
$apiSegments = explode('/', $apiUri);

$resource = $apiSegments[0] ?? null; 
$segment2 = $apiSegments[1] ?? null; 
$segment3 = $apiSegments[2] ?? null; 


switch ($resource) {
    case 'usuarios':
        $userId = $segment2; // User ID
        
        $controller = new UsuariosController();

        if ($userId && $segment3 === 'password') {
            if ($requestMethod === 'PUT') {
                $controller->changePassword($userId);
                $routeHandled = true;
            } else {
                http_response_code(405); // Method Not Allowed
                echo json_encode(["message" => "Método " . $requestMethod . " no permitido para este endpoint de cambio de contraseña."]);
                $routeHandled = true;
            }
        } elseif (count($apiSegments) <= 4) { // Path is /usuarios or /usuarios/{id} (or just /usuarios/{id} if segment3 is null)
             switch ($requestMethod) {
                 case 'GET':
                     if ($userId) { // GET /api/v1/usuarios/{id}
                         $controller->show($userId);
                     } else { // GET /api/v1/usuarios
                         $controller->index();
                     }
                     $routeHandled = true;
                     break;
                 case 'POST':
                     if (!$userId) { // Only POST to /usuarios, not /usuarios/{id}
                        // POST /api/v1/usuarios
                        $controller->store();
                        $routeHandled = true;
                     }
                     break;
                 case 'PUT':
                     // Asegúrate de que no es la ruta de password
                     if ($userId && is_numeric($userId) && $segment3 !== 'password') { // Only PUT to /api/v1/usuarios/{id}
                         $controller->update($userId);
                         $routeHandled = true;
                     }
                     break;
                 case 'DELETE':
                     if ($userId && is_numeric($userId)) { // Only DELETE to /api/v1/usuarios/{id}
                         $controller->destroy($userId);
                         $routeHandled = true;
                     }
                     break;
                 default:
                      http_response_code(405);
                      echo json_encode(["message" => "Método " . $requestMethod . " no permitido para este endpoint de usuarios."]);
                      $routeHandled = true;
                     break;
             }
        }
        // If count($apiSegments) > 4 (e.g., /usuarios/123/password/extra), it falls through to 404 below, which is correct
        break;

    case 'foros':
        $foroId = $segment2; // Foro ID (could be null for /foros)
        $subResource = $segment3; // 'comentarios' or null

        if (!$subResource && count($apiSegments) <= 2) {
             // Validate if segment2 is a numeric ID or if it's not present (for /foros)
             if ($foroId !== null && !is_numeric($foroId)) {
                  // If there is a second segment but it's NOT numeric (e.g., /foros/abc), it's an invalid route for ID
                  http_response_code(400); // Bad Request
                  echo json_encode(["message" => "ID de foro inválido en la ruta."]);
                  $routeHandled = true;
             } else {
                $controller = new ForoController();

                switch ($requestMethod) {
                    case 'GET':
                         if ($foroId) { // GET /api/v1/foros/{id}
                             $controller->show($foroId);
                         } else { // GET /api/v1/foros (with potential query parameters)
                              $controller->index(); // Este método ahora maneja los filtros
                         }
                         $routeHandled = true;
                         break;
                    case 'POST':
                         if (!$foroId) { // Only POST to /api/v1/foros
                              $controller->store();
                              $routeHandled = true;
                         } // If $foroId is present, it's a 405 or 404, handled by default case
                         break;
                    case 'PUT':
                         if ($foroId) { // Only PUT to /api/v1/foros/{id}
                              $controller->update($foroId);
                              $routeHandled = true;
                         }
                         break;
                    case 'DELETE':
                         if ($foroId) { // Only DELETE to /api/v1/foros/{id}
                              $controller->destroy($foroId);
                              $routeHandled = true;
                         }
                         break;
                    default:
                         http_response_code(405); // Method Not Allowed
                         echo json_encode(["message" => "Método " . $requestMethod . " no permitido para este endpoint de foros."]);
                         $routeHandled = true;
                        break;
                }
             }
        }
        // --- Handle /api/v1/foros/{id}/comentarios ---
        // If sub-resource is 'comentarios', the foro ID (segment2) must be numeric, and the number of segments is exactly 3
        elseif ($subResource === 'comentarios' && $foroId && is_numeric($foroId) && count($apiSegments) === 3) {
             $controller = new ComentarioForoController();

             switch ($requestMethod) {
                 case 'GET':
                      $controller->indexByForo($foroId);
                      $routeHandled = true;
                      break;
                 case 'POST':
                       $controller->store($foroId);
                       $routeHandled = true;
                       break;
                 default:
                      http_response_code(405);
                      echo json_encode(["message" => "Método " . $requestMethod . " no permitido para foros/{id}/comentarios."]);
                      $routeHandled = true;
                      break;
             }
        }
        // If the path starts with /foros but doesn't match a valid pattern
        // it falls through to the general 404 handler below.
        break;
    case 'comentarios':
        $commentId = $segment2; // Comment ID

        // Handle the /api/v1/comentarios/{id} route
        // Ensure the path is exactly comentarios/{id} and the ID is numeric
        if ($commentId && is_numeric($commentId) && count($apiSegments) === 2) {
            // Controller must be included in public/index.php
            $controller = new ComentarioForoController();

             switch ($requestMethod) {
                 case 'GET':
                     // GET /api/v1/comentarios/{id}
                     $controller->show($commentId); // $commentId already validated as numeric
                     $routeHandled = true;
                     break;
                 case 'PUT':
                      // PUT /api/v1/comentarios/{id}
                      $controller->update($commentId); // $commentId already validated as numeric
                      $routeHandled = true;
                      break;
                 case 'DELETE':
                      // DELETE /api/v1/comentarios/{id}
                      $controller->destroy($commentId); // $commentId already validated as numeric
                      $routeHandled = true;
                      break;
                 default:
                     http_response_code(405);
                     echo json_encode(["message" => "Método " . $requestMethod . " no permitido para el recurso comentarios/{id}."]);
                     $routeHandled = true;
                     break;
             }
        }
        // If commentId is missing (e.g., /api/v1/comentarios) or the structure is not /comentarios/{id},
        // it is not handled here and falls through to the general 404.
        break;

    case 'animales':
        // **IMPORTANTE**: Evaluar primero las rutas más específicas
        // Si el segundo segmento es 'filter', maneja la ruta de filtro
        if (isset($segment2) && $segment2 === 'filter') {
            $controller = new AnimalesController();
            if ($requestMethod === 'GET') {
                $controller->filter();
                $routeHandled = true;
            } else {
                http_response_code(405); // Método no permitido
                echo json_encode(["message" => "Método " . $requestMethod . " no permitido para /api/v1/animales/filter."]);
                $routeHandled = true;
            }
            break; // Salir del switch 'animales' después de manejar 'animales/filter'
        }

        // --- Lógica Existente para CRUD de Animales (/animales y /animales/{id}) ---

        $animalId = $segment2; // Animal ID (could be null for /animales)

        // Validar que si hay un ID, sea numérico
        if ($animalId && !is_numeric($animalId)) {
            http_response_code(400); // Bad Request
            echo json_encode(["message" => "ID de animal inválido en la ruta."]);
            $routeHandled = true;
            break; // Salir si el ID es inválido
        }

        // Si hay un tercer segmento, es una ruta inválida para CRUD básico
        if (isset($segment3)) {
            // Esto cubrirá casos como /animales/123/extra
            http_response_code(404); // Not Found
            echo json_encode(["message" => "Ruta no encontrada para animales."]);
            $routeHandled = true;
            break;
        }

        // Si llegamos aquí, la ruta es /animales o /animales/{id}
        $controller = new AnimalesController();

        switch ($requestMethod) {
            case 'GET':
                if ($animalId) { // GET /api/v1/animales/{id}
                    $controller->show($animalId);
                } else { // GET /api/v1/animales
                    $controller->index();
                }
                $routeHandled = true;
                break;
            case 'POST':
                if (!$animalId) { // Only POST to /api/v1/animales
                    $controller->store();
                    $routeHandled = true;
                } else { // POST to /api/v1/animales/{id} is not allowed
                    http_response_code(405);
                    echo json_encode(["message" => "Método POST no permitido para /api/v1/animales/{id}."]);
                    $routeHandled = true;
                }
                break;
            case 'PUT':
                if ($animalId) { // Only PUT to /api/v1/animales/{id}
                    $controller->update($animalId);
                    $routeHandled = true;
                } else { // PUT to /api/v1/animales is not allowed
                    http_response_code(405);
                    echo json_encode(["message" => "Método PUT requiere un ID para /api/v1/animales/{id}."]);
                    $routeHandled = true;
                }
                break;
            case 'DELETE':
                if ($animalId) { // Only DELETE to /api/v1/animales/{id}
                    $controller->destroy($animalId);
                    $routeHandled = true;
                } else { // DELETE to /api/v1/animales is not allowed
                    http_response_code(405);
                    echo json_encode(["message" => "Método DELETE requiere un ID para /api/v1/animales/{id}."]);
                    $routeHandled = true;
                }
                break;
            default:
                http_response_code(405); // Método no permitido para esta ruta base
                echo json_encode(["message" => "Método " . $requestMethod . " no permitido para este endpoint."]);
                $routeHandled = true;
                break;
        }
        break; // Salir del switch principal 'segment1'

    case 'noticias': // Caso para Noticias
         $noticiaId = $segment2; // ID de Noticia (puede ser null para /noticias)

         // Manejar /api/v1/noticias y /api/v1/noticias/{id}
      
         if (count($apiSegments) <= 2) { // La ruta es /noticias o /noticias/{id}
              if ($noticiaId && !is_numeric($noticiaId)) {
                   http_response_code(400); // Bad Request
                   echo json_encode(["message" => "ID de noticia inválido en la ruta."]);
                   $routeHandled = true;
              } else {
                 $controller = new NoticiasController();

                 switch ($requestMethod) {
                     case 'GET':
                          if ($noticiaId) { // GET /api/v1/noticias/{id}
                               $controller->show($noticiaId); // $noticiaId ya validado como numérico arriba
                           } elseif (count($apiSegments) === 1) { // GET /api/v1/noticias
                                $controller->index();
                           } 
                           $routeHandled = true;
                           break;
                      case 'POST':
                           if (!$noticiaId && count($apiSegments) === 1) { // Solo POST a /api/v1/noticias
                                $controller->store();
                                $routeHandled = true;
                           } // Si hay ID o más segmentos, cae al 404 o 405
                           break;
                       case 'PUT':
                           if ($noticiaId && is_numeric($noticiaId)) { // Solo PUT a /api/v1/noticias/{id}
                                $controller->update($noticiaId); // $noticiaId ya validado como numérico
                                $routeHandled = true;
                           } // Si no hay ID, cae al 404 o 405
                           break;
                       case 'DELETE':
                           if ($noticiaId && is_numeric($noticiaId)) { // Solo DELETE a /api/v1/noticias/{id}
                                $controller->destroy($noticiaId); // $noticiaId ya validado como numérico
                                $routeHandled = true;
                           } // Si no hay ID, cae al 404 o 405
                           break;
                       default:
                            // Método no permitido para /noticias o /noticias/{id}
                           if (!$routeHandled) { // Asegurarse de manejar 405 solo si la forma de la ruta coincidió pero el método no
                               http_response_code(405);
                               echo json_encode(["message" => "Método " . $requestMethod . " no permitido para este endpoint de noticias."]);
                               $routeHandled = true;
                           }
                          break;
                   }
               }
          }
          break;

    default:

        break;
}


?>