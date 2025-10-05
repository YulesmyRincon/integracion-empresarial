<?php 
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Client;
use Predis\Client as RedisClient;

class ClienteController {

    /**
     * @OA\Get(
     *   path="/api/clients",
     *   summary="Listar clientes",
     *   tags={"Clientes"},
     *   @OA\Response(response=200, description="Lista de clientes")
     * )
     */
    public function index(Request $req, Response $res) {
        $redis = new RedisClient([
            'scheme' => 'tcp',
            'host'   => $_ENV['REDIS_HOST'] ?? 'redis',
            'port'   => $_ENV['REDIS_PORT'] ?? 6379,
        ]);

        $cacheKey = 'clients:list';
        $cached = $redis->get($cacheKey);

        if ($cached) {
            $res->getBody()->write($cached);
            return $res->withHeader('Content-Type','application/json');
        }

        $clients = Client::orderBy('name')->get();
        $json = $clients->toJson();

        // cachear por 60 segundos
        $redis->setex($cacheKey, 60, $json);

        $res->getBody()->write($json);
        return $res->withHeader('Content-Type','application/json');
    }

    /**
     * @OA\Get(
     *   path="/api/clients/{id}",
     *   summary="Obtener cliente por ID",
     *   tags={"Clientes"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Cliente encontrado"),
     *   @OA\Response(response=404, description="Cliente no encontrado")
     * )
     */
    public function show(Request $req, Response $res, $args) {
        $client = Client::find($args['id']);
        if (!$client) {
            $res->getBody()->write(json_encode(['error'=>'Not found']));
            return $res->withStatus(404)->withHeader('Content-Type','application/json');
        }
        $res->getBody()->write($client->toJson());
        return $res->withHeader('Content-Type','application/json');
    }

    /**
     * @OA\Post(
     *   path="/api/clients",
     *   summary="Crear cliente",
     *   tags={"Clientes"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"name","email"},
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="email", type="string"),
     *       @OA\Property(property="phone", type="string"),
     *       @OA\Property(property="address", type="string")
     *     )
     *   ),
     *   @OA\Response(response=201, description="Cliente creado")
     * )
     */
    public function store(Request $req, Response $res) {
        $data = (array)$req->getParsedBody();
        $client = Client::create($data);

        // Invalida cache
        $redis = new RedisClient(['host'=>'redis']);
        $redis->del(['clients:list']);

        $res->getBody()->write($client->toJson());
        return $res->withStatus(201)->withHeader('Content-Type','application/json');
    }

    /**
     * @OA\Put(
     *   path="/api/clients/{id}",
     *   summary="Actualizar cliente",
     *   tags={"Clientes"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="email", type="string"),
     *       @OA\Property(property="phone", type="string"),
     *       @OA\Property(property="address", type="string")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Cliente actualizado"),
     *   @OA\Response(response=404, description="Cliente no encontrado")
     * )
     */
    public function update(Request $req, Response $res, $args) {
        $client = Client::find($args['id']);
        if (!$client) {
            $res->getBody()->write(json_encode(['error'=>'Not found']));
            return $res->withStatus(404)->withHeader('Content-Type','application/json');
        }
        $client->update((array)$req->getParsedBody());

        // Invalida cache
        $redis = new RedisClient(['host'=>'redis']);
        $redis->del(['clients:list']);

        $res->getBody()->write($client->toJson());
        return $res->withHeader('Content-Type','application/json');
    }

    /**
     * @OA\Delete(
     *   path="/api/clients/{id}",
     *   summary="Eliminar cliente",
     *   tags={"Clientes"},
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=204, description="Cliente eliminado"),
     *   @OA\Response(response=404, description="Cliente no encontrado")
     * )
     */
    public function delete(Request $req, Response $res, $args) {
        $client = Client::find($args['id']);
        if (!$client) {
            $res->getBody()->write(json_encode(['error'=>'Not found']));
            return $res->withStatus(404)->withHeader('Content-Type','application/json');
        }
        $client->delete();

        // Invalida cache
        $redis = new RedisClient(['host'=>'redis']);
        $redis->del(['clients:list']);

        return $res->withStatus(204)->withHeader('Content-Type','application/json');
    }
}
