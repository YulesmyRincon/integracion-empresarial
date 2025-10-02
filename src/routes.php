<?php
declare(strict_types=1);

use Slim\App;
use App\Controllers\AuthController;
use App\Controllers\ProductController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

return function (App $app) { 
   
// Rutas públicas de clientes
// ----------------------------

$app->get('/ping', function ($req, $res) {
    $res->getBody()->write(json_encode(['pong' => true]));
    return $res->withHeader('Content-Type', 'application/json');
});


// ----------------------------
// Rutas protegidas de clientes
// ----------------------------
$app->group('/api/clients', function ($group) {
    $group->post('', [ClientController::class, 'store']);
    $group->put('/{id}', [ClientController::class, 'update']);
    $group->delete('/{id}', [ClientController::class, 'delete']);
})->add(new RoleMiddleware(['admin'])) // primero validar rol
  ->add(new AuthMiddleware());         // luego validar token


    // ----------------------------
    // Rutas públicas de autenticación
    // ----------------------------
    $app->post('/api/login', [AuthController::class, 'login']);
    $app->post('/api/register', [AuthController::class, 'register']);

    // ----------------------------
    // Rutas públicas de productos
    // ----------------------------
    $app->get('/api/products', [ProductController::class, 'index']);
    $app->get('/api/products/{id}', [ProductController::class, 'show']);

    // ----------------------------
    // Rutas protegidas solo para ADMIN
    // ----------------------------
    $app->group('/api', function ($group) {
        $group->post('/products', [ProductController::class, 'store']);
        $group->put('/products/{id}', [ProductController::class, 'update']);
        $group->delete('/products/{id}', [ProductController::class, 'destroy']);
    })->add(new RoleMiddleware(['admin'])) // primero validar rol
      ->add(new AuthMiddleware());         // luego validar token

    // ----------------------------
    // Ruta protegida para cualquier usuario autenticado
    // ----------------------------
    $app->get('/api/me', function ($req, $res) {
        $user = $req->getAttribute('user'); // usuario inyectado por AuthMiddleware
        $res->getBody()->write(json_encode($user));
        return $res->withHeader('Content-Type', 'application/json');
    })->add(new AuthMiddleware());

    $app->get('/ping', function ($req, $res) {
    $res->getBody()->write("pong 🚀");
    return $res;
});

};
