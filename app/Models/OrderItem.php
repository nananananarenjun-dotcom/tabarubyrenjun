<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    // Izinkan semua kolom diisi
    protected $guarded = [];

    // 1. Relasi balik ke Pesanan (Order)
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    // 2. Relasi ke Produk (Pastikan mencari product_id)
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}