<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Order extends Model {
    protected $table = 'orders';
    protected $fillable = ['client_id','total','status'];
    public $timestamps = true;

    public function items() {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
