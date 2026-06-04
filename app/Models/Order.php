<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = ['id'];

    // Satu pesanan milik satu pembeli
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

    public function user()
{
    return $this->belongsTo(\App\Models\User::class);
}

    // Satu pesanan bisa punya banyak barang
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Satu pesanan punya satu metode pembayaran
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
    public function userNotifications()
{
    return $this->hasMany(\App\Models\UserNotification::class);
}
}