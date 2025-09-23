<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Product;

class ProductController {
    public function index(Request $req, Response $res) {
        $container = $GLOBALS['app']->getContainer();
        $redis = $container->get('redis');
        $cacheKey = "products_all";
        if($redis->exists($cacheKey)) {
            $data = json_decode($redis->get($cacheKey), true);
            $res->getBody()->write(json_encode($data));
            return $res->withHeader('Content-Type','application/json');
        }
        $products = Product::orderBy('name')->get();
        $redis->setex($cacheKey, 60, $products->toJson());
        $res->getBody()->write($products->toJson());
        return $res->withHeader('Content-Type','application/json');
    }

    public function show(Request $req, Response $res, $args) {
        $product = Product::find($args['id']);
        if(!$product) return $res->withStatus(404)->withHeader('Content-Type','application/json')->write(json_encode(['error'=>'Not found']));
        $res->getBody()->write($product->toJson());
        return $res->withHeader('Content-Type','application/json');
    }

    public function store(Request $req, Response $res) {
        $product = Product::create((array)$req->getParsedBody());
        $GLOBALS['app']->getContainer()->get('redis')->flushdb();
        $res->getBody()->write($product->toJson());
        return $res->withStatus(201)->withHeader('Content-Type','application/json');
    }

    public function update(Request $req, Response $res, $args) {
        $product = Product::find($args['id']);
        if(!$product) return $res->withStatus(404)->withHeader('Content-Type','application/json')->write(json_encode(['error'=>'Not found']));
        $product->update((array)$req->getParsedBody());
        $GLOBALS['app']->getContainer()->get('redis')->flushdb();
        $res->getBody()->write($product->toJson());
        return $res->withHeader('Content-Type','application/json');
    }

    public function delete(Request $req, Response $res, $args) {
        $product = Product::find($args['id']);
        if(!$product) return $res->withStatus(404)->withHeader('Content-Type','application/json')->write(json_encode(['error'=>'Not found']));
        $product->delete();
        $GLOBALS['app']->getContainer()->get('redis')->flushdb();
        return $res->withStatus(204);
    }
}
