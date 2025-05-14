<?php
// Clave secreta para firmar el token
$secret_key = "mi_clave_secreta";

// Función para generar el JWT
function generarJWT($header, $payload, $secret_key) {
    // Codificar en Base64 URL el header y el payload
    $header_encoded = base64UrlEncode(json_encode($header));
    $payload_encoded = base64UrlEncode(json_encode($payload));
    
    // Crear la firma
    $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", $secret_key, true);
    $signature_encoded = base64UrlEncode($signature);
    
    // Combinar todos los elementos en el JWT
    return "$header_encoded.$payload_encoded.$signature_encoded";
}

// Función para verificar el JWT
function verificarJWT($jwt, $secret_key) {
    // Separar el token en sus tres partes
    list($header_encoded, $payload_encoded, $signature_encoded) = explode('.', $jwt);

    // Decodificar las partes del JWT
    $header = json_decode(base64UrlDecode($header_encoded), true);
    $payload = json_decode(base64UrlDecode($payload_encoded), true);

    // Verificar si las partes decodificadas son válidas
    if (!$header || !$payload) {
        return false; // Si no se pudo decodificar el header o el payload, el token es inválido
    }

    // Verificar si el token ha expirado
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return false; // El token ha expirado
    }

    // Crear la firma y verificar si coincide
    $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", $secret_key, true);
    $signature_encoded_check = base64UrlEncode($signature);

    // Verificar que la firma decodificada coincide con la firma proporcionada en el JWT
    return ($signature_encoded_check === $signature_encoded);
}

// Función para decodificar un JWT y obtener su información
function decodificarJWT($jwt) {
    // Separar el token en sus tres partes
    list($header_encoded, $payload_encoded, $signature_encoded) = explode('.', $jwt);

    // Decodificar las partes del JWT
    $header = json_decode(base64UrlDecode($header_encoded), true);
    $payload = json_decode(base64UrlDecode($payload_encoded), true);

    // Devolver el payload (datos del usuario o información contenida en el token)
    return $payload;
}

// Función auxiliar para codificar en Base64 URL seguro
function base64UrlEncode($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}

// Función auxiliar para decodificar Base64 URL seguro
function base64UrlDecode($data) {
    return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
}
