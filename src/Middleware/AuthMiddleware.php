<?php
namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {
    public function __invoke(Request $request, $handler): Response {
        $auth = $request->getHeaderLine('Authorization');
        if (!$auth) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error'=>'Missing Authorization header']));
            return $response->withStatus(401)->withHeader('Content-Type','application/json');
        }

        if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
            $token = $matches[1];
        } else {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error'=>'Invalid Authorization header']));
            return $response->withStatus(400)->withHeader('Content-Type','application/json');
        }

        try {
            $secret = $_ENV['JWT_SECRET'] ?? 'change_this_secret';
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            // coloca info de usuario como array para facilidad
            $user = [
                'id' => $decoded->sub ?? null,
                'name'=> $decoded->name ?? null,
                'email'=> $decoded->email ?? null,
                'role'=> $decoded->role ?? 'user'
            ];
            // inyectar atributo 'user'
            $request = $request->withAttribute('user', $user);
            return $handler->handle($request);
        } catch (\Exception $e) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error'=>'Invalid token', 'message'=>$e->getMessage()]));
            return $response->withStatus(401)->withHeader('Content-Type','application/json');
        }
    }
}
