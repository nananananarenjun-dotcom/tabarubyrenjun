<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $guarded = ['id']; // Bebaskan semua kolom untuk diisi kecuali ID

    // Satu data keranjang pasti memuat satu produk
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Satu data keranjang pasti milik satu user (pembeli)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}