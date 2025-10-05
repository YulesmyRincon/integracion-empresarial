<?php
declare(strict_types=1);

use Slim\App;
use App\Controllers\AuthController;
use App\Controllers\ClientController;
use App\Controllers\ProductController;
use App\Controllers\OrderController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

return function (App $app) {

    // ----------------------------
    // Ruta simple para probar si el servidor responde
    // ----------------------------
    $app->get('/ping', function ($req, $res) {
        $res->getBody()->write(json_encode(['pong' => true, 'status' => 'ok']));
        return $res->withHeader('Content-Type', 'application/json');
    });

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
    // Rutas públicas de clientes (solo lectura)
    // ----------------------------
    $app->get('/api/clients', [ClientController::class, 'index']);
    $app->get('/api/clients/{id}', [ClientController::class, 'show']);

    // ----------------------------
    // Rutas protegidas solo para ADMIN (crear, editar, eliminar)
    // ----------------------------
    $app->group('/api/admin', function ($group) {
        // CLIENTES
        $group->post('/clients', [ClientController::class, 'store']);
        $group->put('/clients/{id}', [ClientController::class, 'update']);
        $group->delete('/clients/{id}', [ClientController::class, 'delete']);

        // PRODUCTOS
        $group->post('/products', [ProductController::class, 'store']);
        $group->put('/products/{id}', [ProductController::class, 'update']);
        $group->delete('/products/{id}', [ProductController::class, 'destroy']);

        // PEDIDOS
        $group->post('/orders', [OrderController::class, 'store']);
        $group->put('/orders/{id}', [OrderController::class, 'update']);
        $group->delete('/orders/{id}', [OrderController::class, 'delete']);
    })
    ->add(new RoleMiddleware(['admin'])) // Primero valida rol
    ->add(new AuthMiddleware());         // Luego valida token

    // ----------------------------
    // Ruta protegida para cualquier usuario autenticado
    // ----------------------------
    $app->get('/api/me', function ($req, $res) {
        $user = $req->getAttribute('user'); // Usuario decodificado del token
        $res->getBody()->write(json_encode($user));
        return $res->withHeader('Content-Type', 'application/json');
    })->add(new AuthMiddleware());

};
