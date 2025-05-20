<?php

switch (true) {

    case $requestUri === '' || $requestUri === '/' || $requestUri === '/home': 
        require_once $controllerDir . 'ControllerHome.php'; 
       
        $routeHandled = true; 
        break;

    case $requestUri === '/register': 
        require_once $controllerDir . 'ControllerRegister.php'; 
        
        $routeHandled = true;
        break;

    
    case $requestUri === '/login':
        
        require_once $controllerDir . 'ControllerLogin.php'; 
     
        $routeHandled = true;
        break;

   
    case $requestUri === '/perfil': 
       
        require_once $controllerDir . 'ControllerPerfil.php'; 
       
        $routeHandled = true;
        break;

    
    case $requestUri === '/animales': 
        require_once $controllerDir . 'ControllerList.php'; 
        $routeHandled = true;
        break;

   
    

   
    case $requestUri === '/forum' && $requestMethod === 'GET': 
        require_once $controllerDir . 'ControllerForo.php';
        
        listarForos(); 
        $routeHandled = true;
        break;

    
    case preg_match('#^/forum/(\d+)$#', $requestUri, $m_foro) === 1 && $requestMethod === 'GET':
        $_GET['id'] = $m_foro[1]; 
        require_once $controllerDir . 'ControllerForoDetail.php'; 
        detalleForo(); 
        $routeHandled = true;
        break;
   
    case preg_match('#^/animales/([^/]+)$#', $requestUri, $matches) === 1 && $requestMethod === 'GET':
        $_GET['nombre'] = $matches[1]; 
        require_once $controllerDir . 'ControllerAnimalDetail.php'; 
        mostrarAnimal(); 
        $routeHandled = true;
        break;
  
     case $requestUri === '/admin':
        require_once $controllerDir . 'ControllerAdmin.php';
        adminPanel(null, 'Bienvenido al panel de administración de MyZoo. Selecciona una opción del menú de la izquierda.');
        $routeHandled = true;
        break;

    case $requestUri === '/admin/users':
        require_once $controllerDir . 'ControllerAdmin.php';
        adminPanel('usuarioAdmin', 'Este es el apartado de gestión de usuarios.');
        $routeHandled = true;
        break;

    case $requestUri === '/admin/foro':
        require_once $controllerDir . 'ControllerAdmin.php';
        adminPanel('foroAdmin', 'Este es el apartado de gestión de foros.');
        $routeHandled = true;
        break;

    case $requestUri === '/admin/noticias':
        require_once $controllerDir . 'ControllerAdmin.php';
        adminPanel('noticiaAdmin', 'Este es el apartado de gestión de noticias.');
        $routeHandled = true;
        break;

    case $requestUri === '/admin/animales':
        require_once $controllerDir . 'ControllerAdmin.php';
        adminPanel('animalAdmin', 'Este es el apartado de gestión de animales.');
        $routeHandled = true;
        break;
 
}
