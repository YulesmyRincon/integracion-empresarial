<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Order;
use Predis\Client as RedisClient;

class OrderController {

    /**
     * @OA\Get(
     *   path="/api/orders",
     *   summary="Listar pedidos",
     *   tags={"Pedidos"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Lista de pedidos")
     * )
     */
    public function index(Request $req, Response $res) {
        $redis = new RedisClient([
            'scheme' => 'tcp',
            'host'   => $_ENV['REDIS_HOST'] ?? 'redis',
            'port'   => $_ENV['REDIS_PORT'] ?? 6379,
        ]);

        $cacheKey = 'orders:list';
        $cached = $redis->get($cacheKey);
        if ($cached) {
            $res->getBody()->write($cached);
            return $res->withHeader('Content-Type','application/json');
        }

        $orders = Order::with('items')->get();
        $json = $orders->toJson();
        $redis->setex($cacheKey, 60, $json);

        $res->getBody()->write($json);
        return $res->withHeader('Content-Type','application/json');
    }

    /**
     * @OA\Get(
     *   path="/api/orders/{id}",
     *   summary="Obtener pedido por ID",
     *   tags={"Pedidos"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Pedido encontrado"),
     *   @OA\Response(response=404, description="Pedido no encontrado")
     * )
     */
    public function show(Request $req, Response $res, $args) {
        $order = Order::with('items')->find($args['id']);
        if (!$order) {
            $res->getBody()->write(json_encode(['error'=>'Not found']));
            return $res->withStatus(404)->withHeader('Content-Type','application/json');
        }
        $res->getBody()->write($order->toJson());
        return $res->withHeader('Content-Type','application/json');
    }

    /**
     * @OA\Post(
     *   path="/api/orders",
     *   summary="Crear pedido",
     *   tags={"Pedidos"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"client_id"},
     *       @OA\Property(property="client_id", type="integer"),
     *       @OA\Property(property="total", type="number", format="float"),
     *       @OA\Property(property="items", type="array", @OA\Items(type="object"))
     *     )
     *   ),
     *   @OA\Response(response=201, description="Pedido creado")
     * )
     */
    public function store(Request $req, Response $res) {
        $data = (array)$req->getParsedBody();
        $order = Order::create($data);

        $redis = new RedisClient(['host' => $_ENV['REDIS_HOST'] ?? 'redis']);
        $redis->del(['orders:list']);

        $res->getBody()->write($order->toJson());
        return $res->withStatus(201)->withHeader('Content-Type','application/json');
    }

    /**
     * @OA\Put(
     *   path="/api/orders/{id}",
     *   summary="Actualizar pedido",
     *   tags={"Pedidos"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="client_id", type="integer"),
     *       @OA\Property(property="total", type="number", format="float")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Pedido actualizado"),
     *   @OA\Response(response=404, description="Pedido no encontrado")
     * )
     */
    public function update(Request $req, Response $res, $args) {
        $order = Order::find($args['id']);
        if (!$order) {
            $res->getBody()->write(json_encode(['error'=>'Not found']));
            return $res->withStatus(404)->withHeader('Content-Type','application/json');
        }
        $order->update((array)$req->getParsedBody());

        $redis = new RedisClient(['host' => $_ENV['REDIS_HOST'] ?? 'redis']);
        $redis->del(['orders:list']);

        $res->getBody()->write($order->toJson());
        return $res->withHeader('Content-Type','application/json');
    }

    /**
     * @OA\Delete(
     *   path="/api/orders/{id}",
     *   summary="Eliminar pedido",
     *   tags={"Pedidos"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=204, description="Pedido eliminado"),
     *   @OA\Response(response=404, description="Pedido no encontrado")
     * )
     */
    public function delete(Request $req, Response $res, $args) {
        $order = Order::find($args['id']);
        if (!$order) {
            $res->getBody()->write(json_encode(['error'=>'Not found']));
            return $res->withStatus(404)->withHeader('Content-Type','application/json');
        }
        $order->delete();

        $redis = new RedisClient(['host' => $_ENV['REDIS_HOST'] ?? 'redis']);
        $redis->del(['orders:list']);

        return $res->withStatus(204)->withHeader('Content-Type','application/json');
    }
}
