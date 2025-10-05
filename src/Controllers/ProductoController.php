<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Product;
use Predis\Client as RedisClient;

class ProductController {

    /**
     * @OA\Get(
     *   path="/api/products",
     *   summary="Listar productos",
     *   tags={"Productos"},
     *   @OA\Response(response=200, description="Lista de productos")
     * )
     */
    public function index(Request $req, Response $res) {
        $redis = new RedisClient([
            'scheme' => 'tcp',
            'host'   => $_ENV['REDIS_HOST'] ?? 'redis',
            'port'   => $_ENV['REDIS_PORT'] ?? 6379,
        ]);

        $cacheKey = 'products:list';
        $cached = $redis->get($cacheKey);
        if ($cached) {
            $res->getBody()->write($cached);
            return $res->withHeader('Content-Type','application/json');
        }

        $products = Product::orderBy('name')->get();
        $json = $products->toJson();
        $redis->setex($cacheKey, 60, $json);

        $res->getBody()->write($json);
        return $res->withHeader('Content-Type','application/json');
    }

    /**
     * @OA\Get(
     *   path="/api/products/{id}",
     *   summary="Obtener producto por ID",
     *   tags={"Productos"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Producto encontrado"),
     *   @OA\Response(response=404, description="Producto no encontrado")
     * )
     */
    public function show(Request $req, Response $res, $args) {
        $product = Product::find($args['id']);
        if (!$product) {
            $res->getBody()->write(json_encode(['error'=>'Not found']));
            return $res->withStatus(404)->withHeader('Content-Type','application/json');
        }
        $res->getBody()->write($product->toJson());
        return $res->withHeader('Content-Type','application/json');
    }

    /**
     * @OA\Post(
     *   path="/api/products",
     *   summary="Crear producto",
     *   tags={"Productos"},
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"name","price"},
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="price", type="number", format="float"),
     *       @OA\Property(property="stock", type="integer")
     *     )
     *   ),
     *   @OA\Response(response=201, description="Producto creado")
     * )
     */
    public function store(Request $req, Response $res) {
        $data = (array)$req->getParsedBody();
        $product = Product::create($data);

        $redis = new RedisClient(['host' => $_ENV['REDIS_HOST'] ?? 'redis']);
        $redis->del(['products:list']);

        $res->getBody()->write($product->toJson());
        return $res->withStatus(201)->withHeader('Content-Type','application/json');
    }

    /**
     * @OA\Put(
     *   path="/api/products/{id}",
     *   summary="Actualizar producto",
     *   tags={"Productos"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="price", type="number", format="float"),
     *       @OA\Property(property="stock", type="integer")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Producto actualizado"),
     *   @OA\Response(response=404, description="Producto no encontrado")
     * )
     */
    public function update(Request $req, Response $res, $args) {
        $product = Product::find($args['id']);
        if (!$product) {
            $res->getBody()->write(json_encode(['error'=>'Not found']));
            return $res->withStatus(404)->withHeader('Content-Type','application/json');
        }
        $product->update((array)$req->getParsedBody());

        $redis = new RedisClient(['host' => $_ENV['REDIS_HOST'] ?? 'redis']);
        $redis->del(['products:list']);

        $res->getBody()->write($product->toJson());
        return $res->withHeader('Content-Type','application/json');
    }

    /**
     * @OA\Delete(
     *   path="/api/products/{id}",
     *   summary="Eliminar producto",
     *   tags={"Productos"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=204, description="Producto eliminado"),
     *   @OA\Response(response=404, description="Producto no encontrado")
     * )
     */
    public function destroy(Request $req, Response $res, $args) {
        $product = Product::find($args['id']);
        if (!$product) {
            $res->getBody()->write(json_encode(['error'=>'Not found']));
            return $res->withStatus(404)->withHeader('Content-Type','application/json');
        }
        $product->delete();

        $redis = new RedisClient(['host' => $_ENV['REDIS_HOST'] ?? 'redis']);
        $redis->del(['products:list']);

        return $res->withStatus(204)->withHeader('Content-Type','application/json');
    }
}
