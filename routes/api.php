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

// Divide $apiUri into segments
$apiSegments = explode('/', $apiUri);

// Analyze segments to identify the main resource and sub-resources/IDs
$resource = $apiSegments[0] ?? null; // 'usuarios', 'foros', 'comentarios', 'animales', 'noticias', etc.
$segment2 = $apiSegments[1] ?? null; // Could be an ID (user, comment, foro, animal, noticia) or a sub-resource (e.g., 'comentarios')
$segment3 = $apiSegments[2] ?? null; // Could be a sub-resource or an ID in nested routes


// API routing based on the main resource and HTTP method
switch ($resource) {
    case 'usuarios':
        $userId = $segment2; // User ID
        // Ensure the path is exactly /usuarios or /usuarios/{id} or /usuarios/{id}/password
        
        // Cargar el controlador de usuarios
        $controller = new UsuariosController();

        // Manejar la ruta específica para cambiar contraseña
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

        // --- Handle /api/v1/foros and /api/v1/foros/{id} ---
        // If NO sub-resource (segment3 is null) and the number of segments is 1 or 2
        if (!$subResource && count($apiSegments) <= 2) {
             // Ensure that if there is an ID (segment2), it is numeric
             if ($foroId && !is_numeric($foroId)) {
                  // If there is a second segment but it's NOT numeric (e.g., /foros/abc), it's an invalid route
                  http_response_code(400); // Bad Request
                  echo json_encode(["message" => "ID de foro inválido en la ruta."]);
                  $routeHandled = true;
             } else {
                // Controller must be included in public/index.php
                $controller = new ForoController();

                switch ($requestMethod) {
                    case 'GET':
                         if ($foroId) { // GET /api/v1/foros/{id}
                             $controller->show($foroId); // $foroId already validated as numeric above
                         } elseif (count($apiSegments) === 1) { // GET /api/v1/foros
                              $controller->index();
                         } // If $foroId is null and count>1 (e.g. /foros/extra), falls through to general 404
                         $routeHandled = true; // If reached here, GET was handled or will fall through to 404
                         break;
                    case 'POST':
                         if (!$foroId && count($apiSegments) === 1) { // Only POST to /api/v1/foros
                              $controller->store();
                              $routeHandled = true;
                         } // If there is an ID or more segments, falls through to 404 or 405
                         break;
                    case 'PUT':
                         if ($foroId && is_numeric($foroId)) { // Only PUT to /api/v1/foros/{id}
                              $controller->update($foroId); // $foroId already validated as numeric
                              $routeHandled = true;
                         } // If no ID, falls through to 404 or 405
                         break;
                    case 'DELETE':
                         if ($foroId && is_numeric($foroId)) { // Only DELETE to /api/v1/foros/{id}
                              $controller->destroy($foroId); // $foroId already validated as numeric
                              $routeHandled = true;
                         } // If no ID, falls through to 404 o 405
                         break;
                    default:
                         // Method not allowed for /foros or /foros/{id}
                         http_response_code(405);
                         echo json_encode(["message" => "Método " . $requestMethod . " no permitido para este endpoint de foros."]);
                         $routeHandled = true;
                        break;
                }
             }
        }
        // --- Handle /api/v1/foros/{id}/comentarios ---
        // If sub-resource is 'comentarios', the foro ID (segment2) must be numeric, and the number of segments is exactly 3
        elseif ($subResource === 'comentarios' && $foroId && is_numeric($foroId) && count($apiSegments) === 3) {
             // Controller must be included in public/index.php
             $controller = new ComentarioForoController();

             switch ($requestMethod) {
                 case 'GET':
                      // GET /api/v1/foros/{foro_id}/comentarios
                      $controller->indexByForo($foroId); // $foroId already validated as numeric
                      $routeHandled = true;
                      break;
                 case 'POST':
                       // POST /api/v1/foros/{foro_id}/comentarios
                       $controller->store($foroId); // Pass foroId to store ($foroId ya validado)
                       $routeHandled = true;
                       break;
                 // No PUT/DELETE defined for this nested URL (use /api/v1/comentarios/{id} endpoints)
                 default:
                      http_response_code(405);
                      echo json_encode(["message" => "Método " . $requestMethod . " no permitido para foros/{id}/comentarios."]);
                      $routeHandled = true;
                      break;
             }
        }
        // If the path starts with /foros but doesn't match /foros, /foros/{id}, or /foros/{id}/comentarios,
        // it is not handled here and falls through to the general 404.
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
         $animalId = $segment2; // Animal ID (could be null for /animales)

         // Handle /api/v1/animales and /api/v1/animales/{id}
         // Ensure the path is exactly /animales or /animales/{id} (no more segments)
         if (count($apiSegments) <= 2) { // Path is /animales or /animales/{id}
              // Ensure that if there is an ID (segment2), it is numeric
              if ($animalId && !is_numeric($animalId)) {
                   // If there is a second segment but it's NOT numeric (e.g., /animales/abc), it's an invalid route
                   http_response_code(400); // Bad Request
                   echo json_encode(["message" => "ID de animal inválido en la ruta."]);
                   $routeHandled = true;
              } else {
                // Controller must be included in public/index.php
                 $controller = new AnimalesController();

                 switch ($requestMethod) {
                     case 'GET':
                          if ($animalId) { // GET /api/v1/animales/{id}
                              $controller->show($animalId); // $animalId already validated as numeric above
                          } elseif (count($apiSegments) === 1) { // GET /api/v1/animales
                               $controller->index();
                          } // If $animalId is null and count>1 (e.g. /animales/extra), falls through to general 404
                          $routeHandled = true; // If reached here, GET was handled or will fall through to 404
                          break;
                     case 'POST':
                          if (!$animalId && count($apiSegments) === 1) { // Only POST to /api/v1/animales
                               $controller->store();
                               $routeHandled = true;
                          } // If there is an ID or more segments, falls through to 404 o 405
                           // Optional: Handle POST to /animales/{id} with 405 or 404? Current logic falls through.
                          break;
                     case 'PUT':
                          if ($animalId && is_numeric($animalId)) { // Only PUT to /api/v1/animales/{id}
                               $controller->update($animalId); // $animalId already validated as numeric
                               $routeHandled = true;
                          } // If no ID, falls through to 404 o 405
                           // Optional: Handle PUT to /animales with 405? Current logic falls through.
                          break;
                     case 'DELETE':
                          if ($animalId && is_numeric($animalId)) { // Only DELETE to /api/v1/animales/{id}
                               $controller->destroy($animalId); // $animalId already validated as numeric
                               $routeHandled = true;
                          } // If no ID, falls through to 404 o 405
                           // Optional: Handle DELETE to /animales with 405? Current logic falls through.
                          break;
                     default:
                          // Method not allowed for /animales or /animales/{id}
                           // If $routeHandled is not true, this route didn't match the shape *and* method.
                           // This default handles methods not allowed for the *matched* shapes (/animales, /animales/{id}).
                           // If the shape didn't match either (/animales/extra), it falls through.
                          if (!$routeHandled) { // Ensure we only handle 405 if the shape was /animales or /animales/{id} but method was wrong
                              http_response_code(405);
                              echo json_encode(["message" => "Método " . $requestMethod . " no permitido para este endpoint de animales."]);
                              $routeHandled = true;
                          }
                         break;
                 }
               // Add an extra check: if count($apiSegments) is 1 or 2, but $routeHandled is still false, it means the method wasn't allowed for that shape. The default case above handles this.
              }
         }
         // If the path starts with /animales but doesn't match /animales or /animales/{id},
         // it is not handled here and falls through to the general 404.
         break;

    case 'noticias': // Caso para Noticias
         $noticiaId = $segment2; // ID de Noticia (puede ser null para /noticias)

         // Manejar /api/v1/noticias y /api/v1/noticias/{id}
         // Asegurarse de que la ruta es exactamente /noticias o /noticias/{id} (no más segmentos)
         if (count($apiSegments) <= 2) { // La ruta es /noticias o /noticias/{id}
              // Asegurarse de que si hay un ID (segment2), sea numérico
              if ($noticiaId && !is_numeric($noticiaId)) {
                   // Si hay un segundo segmento pero NO es numérico (ej: /noticias/abc), es una ruta inválida
                   http_response_code(400); // Bad Request
                   echo json_encode(["message" => "ID de noticia inválido en la ruta."]);
                   $routeHandled = true;
              } else {
                // ¡El controlador NoticiasController DEBE HABER SIDO INCLUIDO EN public/index.php!
                 $controller = new NoticiasController();

                 switch ($requestMethod) {
                     case 'GET':
                          if ($noticiaId) { // GET /api/v1/noticias/{id}
                               $controller->show($noticiaId); // $noticiaId ya validado como numérico arriba
                           } elseif (count($apiSegments) === 1) { // GET /api/v1/noticias
                                $controller->index();
                           } // Si $noticiaId es null y count>1 (ej: /noticias/extra), cae al 404 general
                           $routeHandled = true; // Si llegó aquí, GET fue manejado o caerá al 404
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
          // Si la ruta empieza con /noticias pero no coincide con /noticias o /noticias/{id}, cae al 404 general.
          break;

    default:
        // El recurso principal no coincide, o la estructura general de la URL es inválida.
        // No se maneja aquí ($routeHandled sigue siendo false) y caerá al 404 en public/index.php.
        break;
}

// ... (código restante de api.php para manejo del 404 general) ...

?>