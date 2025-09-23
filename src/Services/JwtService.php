<?php
namespace App\Services;

use Firebase\JWT\JWT;

class JwtService {
    public function generateToken($payload) {
        $key = $_ENV['JWT_SECRET'];
        $issuedAt = time();
        $expire = $issuedAt + 3600;

        $token = [
            "iat" => $issuedAt,
            "exp" => $expire,
            "data" => $payload
        ];

        return JWT::encode($token, $key, 'HS256');
    }
}
