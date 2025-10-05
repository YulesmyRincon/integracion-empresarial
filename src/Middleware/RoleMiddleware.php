<?php
namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class RoleMiddleware {
    private $roles;
    public function __construct(array $roles) {
        $this->roles = $roles;
    }

    public function __invoke(Request $request, $handler): Response {
        $user = $request->getAttribute('user');
        if (!$user || !in_array($user['role'], $this->roles)) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error'=>'Forbidden']));
            return $response->withStatus(403)->withHeader('Content-Type','application/json');
        }
        return $handler->handle($request);
    }
}
