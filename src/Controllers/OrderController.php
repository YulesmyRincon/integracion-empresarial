<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Capsule\Manager as DB;

class OrderController {
    public function store(Request $req, Response $res) {
        $data = (array)$req->getParsedBody();
        if(empty($data['client_id']) || empty($data['items']) || !is_array($data['items'])) {
            return $res->withStatus(400)->withHeader('Content-Type','application/json')->write(json_encode(['error'=>'Invalid payload']));
        }
        try {
            DB::beginTransaction();
            $total = 0;
            foreach($data['items'] as $it) {
                $p = Product::find($it['product_id']);
                if(!$p) throw new \Exception("Product {$it['product_id']} not found");
                if($p->stock < $it['qty']) throw new \Exception("Insufficient stock for product {$p->id}");
                $total += ($p->price * $it['qty']);
            }
            $order = Order::create(['client_id'=>$data['client_id'],'total'=>$total,'status'=>'pending']);
            foreach($data['items'] as $it) {
                $p = Product::find($it['product_id']);
                DB::table('order_items')->insert(['order_id'=>$order->id,'product_id'=>$p->id,'qty'=>$it['qty'],'price'=>$p->price]);
                $p->stock -= $it['qty'];
                $p->save();
            }
            DB::commit();
            return $res->withStatus(201)->withHeader('Content-Type','application/json')->write($order->toJson());
        } catch(\Exception $e) {
            DB::rollBack();
            return $res->withStatus(400)->withHeader('Content-Type','application/json')->write(json_encode(['error'=>$e->getMessage()]));
        }
    }

    public function index(Request $req, Response $res) {
        $orders = Order::orderBy('created_at','desc')->get();
        $res->getBody()->write($orders->toJson());
        return $res->withHeader('Content-Type','application/json');
    }

    public function show(Request $req, Response $res, $args) {
        $order = Order::with('items')->find($args['id']);
        if(!$order) return $res->withStatus(404)->withHeader('Content-Type','application/json')->write(json_encode(['error'=>'Not found']));
        $res->getBody()->write($order->toJson());
        return $res->withHeader('Content-Type','application/json');
    }
}
