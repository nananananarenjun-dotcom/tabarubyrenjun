<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = ['id'];

    // Relasi Polymorphic (Bisa terhubung ke Order atau TrainingRegistration)
    public function payable()
    {
        return $this->morphTo();
    }
    

    // Relasi ke metode pembayaran (Bank/QRIS)
    public function method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
}