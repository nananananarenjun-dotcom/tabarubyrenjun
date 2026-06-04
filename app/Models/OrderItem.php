<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $guarded = ['id'];

    // Item ini masuk ke pesanan mana?
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Item ini produknya apa?
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}