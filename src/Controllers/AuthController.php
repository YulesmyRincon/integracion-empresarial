<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {

    public function login(Request $request, Response $response) {
        $body = (array)$request->getParsedBody();
        $email = $body['email'] ?? null;
        $password = $body['password'] ?? null;

        if (!$email || !$password) {
            $response->getBody()->write(json_encode(['error' => 'Email and password required']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $user = User::where('email', $email)->first();
        if (!$user || !password_verify($password, $user->password)) {
            $response->getBody()->write(json_encode(['error' => 'Invalid credentials']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $now = time();
        $payload = [
            'iat' => $now,
            'exp' => $now + 3600, // 1h
            'sub' => $user->id,
            'role' => $user->role,
            'email' => $user->email,
            'name' => $user->name
        ];

        $jwt = JWT::encode($payload, getenv('JWT_SECRET') ?: 'change_me', 'HS256');

        $response->getBody()->write(json_encode(['token' => $jwt, 'user' => ['id'=>$user->id,'email'=>$user->email,'name'=>$user->name, 'role'=>$user->role]]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function register(Request $request, Response $response) {
        $body = (array)$request->getParsedBody();
        $name = $body['name'] ?? null;
        $email = $body['email'] ?? null;
        $password = $body['password'] ?? null;

        if (!$name || !$email || !$password) {
            $response->getBody()->write(json_encode(['error' => 'Name, email and password required']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        if (User::where('email', $email)->exists()) {
            $response->getBody()->write(json_encode(['error' => 'Email already registered']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role' => $body['role'] ?? 'user'
        ]);

        $response->getBody()->write(json_encode(['user' => $user]));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }
}
