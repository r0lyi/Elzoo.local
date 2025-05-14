<?php
// ControllerAdmin.php

// Cargamos los controladores y modelos necesarios
require_once __DIR__ . '/../controllers/ControllerJWT.php';
require_once __DIR__ . '/../controllers/ControllerCookie.php';
require_once __DIR__ . '/../controllers/ControllerTwig.php';
require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/../models/Noticias.php';
require_once __DIR__ . '/../models/Animales.php';
require_once __DIR__ . '/../models/Foro.php';

class ControllerAdmin {

    /**
     * Método para renderizar la vista de administración
     * Verifica la autenticación mediante JWT y obtiene las noticias para mostrarlas.
     */
    public function admin() {
        $jwt = getAuthCookie();
        $noticias = Noticias::getNoticias();
        $isAuthenticated = false;

        if ($jwt && verificarJWT($jwt, 'mi_clave_secreta')) {
            $isAuthenticated = true;
        } else {
            deleteAuthCookie(); // Elimina la cookie si el token es inválido
        }

        renderView('admin.html.twig', [
            'noticias' => $noticias,
            'is_authenticated' => $isAuthenticated
        ]);
        exit;
    }

}