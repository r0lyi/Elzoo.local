<?php

$requestUriFromApiScope = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (str_starts_with($requestUriFromApiScope, '/api/v1/')) {
    $apiUri = substr($requestUriFromApiScope, strlen('/api/v1'));
    $apiUri = trim($apiUri, '/');
} else {
    error_log("ERROR: routes/api.php included, but request URI does not start with /api/v1/: " . $requestUriFromApiScope);
    http_response_code(500); 
    echo json_encode(["message" => "Internal API routing error."]);
    exit();
}

$apiSegments = explode('/', $apiUri);

$resource = $apiSegments[0] ?? null; 
$segment2 = $apiSegments[1] ?? null; 
$segment3 = $apiSegments[2] ?? null; 



switch ($resource) {
    case 'usuarios':
<<<<<<< HEAD
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
=======
        $userId = $segment2; 
        // PUT /api/v1/usuarios/{id}/password
        if ($userId && is_numeric($userId) && $segment3 === 'password' && count($apiSegments) === 3) {
            if ($requestMethod === 'PUT') {
                $controller = new UsuariosController();
                $controller->updatePassword($userId);
                $routeHandled = true;
            } else {
                http_response_code(405);
                echo json_encode(["message" => "Método " . $requestMethod . " no permitido para este endpoint de contraseña."]);
                $routeHandled = true;
            }
            break; // Salir del switch 'usuarios' después de manejar la contraseña
        }
        if (count($apiSegments) <= 2) { 
             $controller = new UsuariosController();
>>>>>>> 47681f718722320eb63a6f86a8caf01617e60a57
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
                     if (!$userId) { // POST /api/v1/usuarios
                        $controller->store();
                        $routeHandled = true;
                     }
                     break;
                 case 'PUT':
<<<<<<< HEAD
                     // Asegúrate de que no es la ruta de password
                     if ($userId && is_numeric($userId) && $segment3 !== 'password') { // Only PUT to /api/v1/usuarios/{id}
=======
                     if ($userId && is_numeric($userId)) { //  PUT to /api/v1/usuarios/{id}
>>>>>>> 47681f718722320eb63a6f86a8caf01617e60a57
                         $controller->update($userId);
                         $routeHandled = true;
                     }
                     break;
                 case 'DELETE':
                     if ($userId && is_numeric($userId)) { //  DELETE to /api/v1/usuarios/{id}
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
<<<<<<< HEAD
        // If count($apiSegments) > 4 (e.g., /usuarios/123/password/extra), it falls through to 404 below, which is correct
=======
>>>>>>> 47681f718722320eb63a6f86a8caf01617e60a57
        break;

    case 'foros':
        $foroId = $segment2; 
        $subResource = $segment3; 
        if (!$subResource && count($apiSegments) <= 2) {
             if ($foroId && !is_numeric($foroId)) {
                  http_response_code(400); 
                  echo json_encode(["message" => "ID de foro inválido en la ruta."]);
                  $routeHandled = true;
             } else {
                $controller = new ForoController();

                switch ($requestMethod) {
                    case 'GET':
                         if ($foroId) { 
                             $controller->show($foroId); 
                         } elseif (count($apiSegments) === 1) { 
                              $controller->index();
                         } 
                         $routeHandled = true; 
                         break;
                    case 'POST':
                         if (!$foroId && count($apiSegments) === 1) { 
                              $controller->store();
                              $routeHandled = true;
                         } 
                         break;
                    case 'PUT':
                         if ($foroId && is_numeric($foroId)) { 
                              $controller->update($foroId);
                              $routeHandled = true;
                         } 
                         break;
                    case 'DELETE':
                         if ($foroId && is_numeric($foroId)) { 
                              $controller->destroy($foroId); 
                              $routeHandled = true;
                         } 
                         break;
                    default:
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
                      $controller->indexByForo($foroId);
                      $routeHandled = true;
                      break;
                 case 'POST':
                       // POST /api/v1/foros/{foro_id}/comentarios
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
       
        break;

    case 'comentarios':
        $commentId = $segment2; 

       
        if ($commentId && is_numeric($commentId) && count($apiSegments) === 2) {
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
                      $controller->destroy($commentId); 
                      $routeHandled = true;
                      break;
                 default:
                     http_response_code(405);
                     echo json_encode(["message" => "Método " . $requestMethod . " no permitido para el recurso comentarios/{id}."]);
                     $routeHandled = true;
                     break;
             }
        }
      
        break;

    case 'animales':
         $animalId = $segment2; 

         
         if (count($apiSegments) <= 2) {
          
              if ($animalId && !is_numeric($animalId)) {
                   http_response_code(400); 
                   echo json_encode(["message" => "ID de animal inválido en la ruta."]);
                   $routeHandled = true;
              } else {
                 $controller = new AnimalesController();

                 switch ($requestMethod) {
                     case 'GET':
                          if ($animalId) { // GET /api/v1/animales/{id}
                              $controller->show($animalId);
                          } elseif (count($apiSegments) === 1) { // GET /api/v1/animales
                               $controller->index();
                          } 
                          $routeHandled = true; 
                          break;
                     case 'POST':
                          if (!$animalId && count($apiSegments) === 1) { // POST to /api/v1/animales
                               $controller->store();
                               $routeHandled = true;
                          } 
                          break;
                     case 'PUT':
                          if ($animalId && is_numeric($animalId)) { //  PUT to /api/v1/animales/{id}
                               $controller->update($animalId); 
                               $routeHandled = true;
                          }
                          break;
                     case 'DELETE':
                          if ($animalId && is_numeric($animalId)) { // DELETE to /api/v1/animales/{id}
                               $controller->destroy($animalId); 
                               $routeHandled = true;
                          } 
                          break;
                     default:
                         
                          if (!$routeHandled) {
                              http_response_code(405);
                              echo json_encode(["message" => "Método " . $requestMethod . " no permitido para este endpoint de animales."]);
                              $routeHandled = true;
                          }
                         break;
                 }
              }
         }

         break;

    case 'noticias': 
         $noticiaId = $segment2; 
        
         if (count($apiSegments) <= 2) {
             
              if ($noticiaId && !is_numeric($noticiaId)) {
                   http_response_code(400); 
                   echo json_encode(["message" => "ID de noticia inválido en la ruta."]);
                   $routeHandled = true;
              } else {
                 $controller = new NoticiasController();

                 switch ($requestMethod) {
                     case 'GET':
                          if ($noticiaId) { // GET /api/v1/noticias/{id}
                               $controller->show($noticiaId); 
                           } elseif (count($apiSegments) === 1) { // GET /api/v1/noticias
                                $controller->index();
                           } 
                           $routeHandled = true; 
                           break;
                      case 'POST':
                           if (!$noticiaId && count($apiSegments) === 1) { //  POST a /api/v1/noticias
                                $controller->store();
                                $routeHandled = true;
                           } 
                           break;
                       case 'PUT':
                           if ($noticiaId && is_numeric($noticiaId)) { //  PUT a /api/v1/noticias/{id}
                                $controller->update($noticiaId); 
                                $routeHandled = true;
                           }
                           break;
                       case 'DELETE':
                           if ($noticiaId && is_numeric($noticiaId)) { //  DELETE a /api/v1/noticias/{id}
                                $controller->destroy($noticiaId); 
                                $routeHandled = true;
                           } 
                           break;
                       default:
                           
                           if (!$routeHandled) { 
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