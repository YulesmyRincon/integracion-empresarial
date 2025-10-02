<?php
namespace App\Middleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;

class AuthMiddleware {
    public function __invoke(Request $request, Handler $handler): Response {
        // Puedes procesar claims si quieres
        return $handler->handle($request);
    }
}
