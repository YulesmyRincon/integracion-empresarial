<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Product;

class ProductController {

    private $redis;

    public function __construct() {
        $this->redis = $GLOBALS['redis_client'] ?? null;
    }

    // GET /api/products
    public function index(Request $request, Response $response) {
        try {
            $params = $request->getQueryParams();
            $page = max(1, (int)($params['page'] ?? 1));
            $perPage = min(100, max(1, (int)($params['per_page'] ?? 10)));
            $cacheKey = "products:page:{$page}:per:{$perPage}";

            // Cache Redis
            if ($this->redis) {
                $cached = $this->redis->get($cacheKey);
                if ($cached) {
                    $response->getBody()->write($cached);
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }

            // DB query
            $query = Product::query();
            $total = $query->count();
            $products = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

            $payload = [
                'data' => $products,
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $perPage
                ]
            ];

            $json = json_encode($payload);

            // Guardar en cache
            if ($this->redis) {
                $this->redis->setex($cacheKey, 60, $json);
            }

            $response->getBody()->write($json);
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Throwable $e) {
            return $this->errorResponse($response, $e->getMessage());
        }
    }

    // GET /api/products/{id}
    public function show(Request $request, Response $response, $args) {
        try {
            $id = (int)$args['id'];
            $product = Product::find($id);

            if (!$product) {
                return $this->errorResponse($response, 'Product not found', 404);
            }

            $response->getBody()->write($product->toJson());
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Throwable $e) {
            return $this->errorResponse($response, $e->getMessage());
        }
    }

    // POST /api/products
    public function store(Request $request, Response $response) {
        try {
            $data = (array)$request->getParsedBody();
            $product = Product::create([
                'name' => $data['name'] ?? 'Untitled',
                'description' => $data['description'] ?? '',
                'price' => $data['price'] ?? 0,
                'stock' => $data['stock'] ?? 0
            ]);

            $this->clearCache();

            $response->getBody()->write($product->toJson());
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');

        } catch (\Throwable $e) {
            return $this->errorResponse($response, $e->getMessage());
        }
    }

    // PUT /api/products/{id}
    public function update(Request $request, Response $response, $args) {
        try {
            $id = (int)$args['id'];
            $product = Product::find($id);

            if (!$product) {
                return $this->errorResponse($response, 'Product not found', 404);
            }

            $data = (array)$request->getParsedBody();
            $product->fill($data);
            $product->save();

            $this->clearCache();

            $response->getBody()->write($product->toJson());
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Throwable $e) {
            return $this->errorResponse($response, $e->getMessage());
        }
    }

    // DELETE /api/products/{id}
    public function destroy(Request $request, Response $response, $args) {
        try {
            $id = (int)$args['id'];
            $product = Product::find($id);

            if (!$product) {
                return $this->errorResponse($response, 'Product not found', 404);
            }

            $product->delete();
            $this->clearCache();

            $response->getBody()->write(json_encode(['success' => true]));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Throwable $e) {
            return $this->errorResponse($response, $e->getMessage());
        }
    }

    // ------------------------
    // Helpers
    // ------------------------
    private function clearCache() {
        if ($this->redis) {
            $keys = $this->redis->keys('products:*');
            foreach ($keys as $k) {
                $this->redis->del($k);
            }
        }
    }

    private function errorResponse(Response $response, string $message, int $status = 500) {
        $payload = json_encode(['error' => $message]);
        $response->getBody()->write($payload);
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }
}
