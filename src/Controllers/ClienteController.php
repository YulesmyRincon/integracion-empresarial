<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Client;

/**
 * @OA\Get(
 *   path="/api/v1/clients",
 *   summary="List clients",
 *   @OA\Response(response=200, description="List")
 * )
 */
class ClientController {
    public function index(Request $req, Response $res) {
        $clients = Client::orderBy('name')->get();
        $res->getBody()->write($clients->toJson());
        return $res->withHeader('Content-Type','application/json');
    }

    public function show(Request $req, Response $res, $args) {
        $client = Client::find($args['id']);
        if(!$client) return $res->withStatus(404)->withHeader('Content-Type','application/json')->write(json_encode(['error'=>'Not found']));
        $res->getBody()->write($client->toJson());
        return $res->withHeader('Content-Type','application/json');
    }

    public function store(Request $req, Response $res) {
        $data = (array)$req->getParsedBody();
        $client = Client::create($data);
        // invalidate cache if used
        $res->getBody()->write($client->toJson());
        return $res->withStatus(201)->withHeader('Content-Type','application/json');
    }

    public function update(Request $req, Response $res, $args) {
        $client = Client::find($args['id']);
        if(!$client) return $res->withStatus(404)->withHeader('Content-Type','application/json')->write(json_encode(['error'=>'Not found']));
        $client->update((array)$req->getParsedBody());
        $res->getBody()->write($client->toJson());
        return $res->withHeader('Content-Type','application/json');
    }

    public function delete(Request $req, Response $res, $args) {
        $client = Client::find($args['id']);
        if(!$client) return $res->withStatus(404)->withHeader('Content-Type','application/json')->write(json_encode(['error'=>'Not found']));
        $client->delete();
        return $res->withStatus(204);
    }
}
