<?php
namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class RoleMiddleware {
    protected array $allowedRoles;

    public function __construct(array $allowedRoles = []) {
        $this->allowedRoles = $allowedRoles;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response {
        $user = $request->getAttribute('user');
        if (!$user) {
            $res = new \Slim\Psr7\Response();
            $res->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $res->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        $role = $user->role ?? ($user->role ?? null);
        // decoded JWT might be an object
        if (is_object($user) && property_exists($user, 'role')) $role = $user->role;
        if (!in_array($role, $this->allowedRoles)) {
            $res = new \Slim\Psr7\Response();
            $res->getBody()->write(json_encode(['error' => 'Forbidden']));
            return $res->withStatus(403)->withHeader('Content-Type', 'application/json');
        }
        return $handler->handle($request);
    }
}
