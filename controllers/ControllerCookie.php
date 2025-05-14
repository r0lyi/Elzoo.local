<?php

// Establecer una cookie de autenticación
function setAuthCookie($jwt, $expirationTime) {
    // Asegurarse de que el valor de expiración sea un timestamp válido
    if ($expirationTime < time()) {
        // Si la expiración ya ha pasado, configuramos la cookie para que expire inmediatamente
        $expirationTime = time() + 3600; // Establecer un valor por defecto si es necesario
    }
    
    setcookie(
        'auth_token',      // Nombre de la cookie
        $jwt,              // Valor (JWT token)
        $expirationTime,   // Tiempo de expiración (en formato timestamp)
        '/',               // Ruta (puede ajustar según lo necesario)
        '',                // Dominio (opcional, solo lo necesitas si usas un dominio diferente)
        false,              // Secure (requiere HTTPS en producción)
        false               // HttpOnly (solo accesible desde el servidor, evita acceso desde JavaScript)
    );
}

// Obtener el valor de la cookie de autenticación
function getAuthCookie() {
    return $_COOKIE['auth_token'] ?? null;
}



// Borrar la cookie de autenticación
//function deleteAuthCookie() {
 //   setcookie('auth_token', '', time() - 3600, '/', '', true, true); // Establecer un tiempo pasado para eliminarla
//}

function deleteAuthCookie() {
    if (isset($_COOKIE['auth_token'])) {
        setcookie('auth_token', '', time() - 3600, '/'); // Expira en el pasado
        unset($_COOKIE['auth_token']); // Elimina del array superglobal
    }
}
?>
