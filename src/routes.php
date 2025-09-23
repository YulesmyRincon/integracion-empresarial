<?php
use Slim\App;
use App\Controllers\AuthController;
use App\Controllers\ClientController;
use App\Controllers\ProductController;
use App\Controllers\OrderController;
use Tuupola\Middleware\JwtAuthentication;

return function(App $app) {
    // Public auth
    $app->post('/api/v1/auth/register', AuthController::class . ':register');
    $app->post('/api/v1/auth/login', AuthController::class . ':login');

    // JWT middleware group
    $app->group('/api/v1', function($group) {
        // Clients
        $group->get('/clients', ClientController::class . ':index');
        $group->get('/clients/{id}', ClientController::class . ':show');
        $group->post('/clients', ClientController::class . ':store');
        $group->put('/clients/{id}', ClientController::class . ':update');
        $group->delete('/clients/{id}', ClientController::class . ':delete');

        // Products
        $group->get('/products', ProductController::class . ':index');
        $group->get('/products/{id}', ProductController::class . ':show');
        $group->post('/products', ProductController::class . ':store');
        $group->put('/products/{id}', ProductController::class . ':update');
        $group->delete('/products/{id}', ProductController::class . ':delete');

        // Orders
        $group->post('/orders', OrderController::class . ':store');
        $group->get('/orders', OrderController::class . ':index');
        $group->get('/orders/{id}', OrderController::class . ':show');
    })->add(new JwtAuthentication([
        "attribute" => "decoded_token_data",
        "secret" => getenv('JWT_SECRET'),
        "algorithm" => ["HS256"],
        "secure" => false,
        "relaxed" => ["localhost", "127.0.0.1"],
        "error" => function ($response, $arguments) {
            $data = ["error" => "Token error: " . $arguments["message"]];
            $response->getBody()->write(json_encode($data));
            return $response->withHeader("Content-Type", "application/json")->withStatus(401);
        }
    ]));
};
