<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $guarded = ['id'];

    // Satu metode pembayaran bisa dipakai oleh banyak transaksi
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}