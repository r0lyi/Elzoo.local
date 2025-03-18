<?php
namespace Elzoo\Core;

class Response {
    /**
     * Envía una respuesta JSON con el código de estado HTTP adecuado.
     *
     * @param mixed $data Datos a enviar en formato JSON.
     * @param int $statusCode Código de estado HTTP (por defecto: 200).
     */
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Envía un mensaje de error en formato JSON.
     *
     * @param string $message Mensaje de error.
     * @param int $statusCode Código de estado HTTP (por defecto: 400).
     */
    public static function error($message, $statusCode = 400) {
        self::json([
            'success' => false,
            'error' => $message
        ], $statusCode);
    }

    /**
     * Redirige a una URL específica.
     *
     * @param string $url URL a la que se redirigirá.
     */
    public static function redirect($url) {
        header("Location: $url");
        exit;
    }
}