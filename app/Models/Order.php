<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // 1. Beritahu Laravel kalau PK-nya pakai string order_id (TRX001)
    protected $primaryKey = 'order_id';
    public $incrementing = false;
    protected $keyType = 'string';

    // 2. Izinkan semua kolom diisi
    protected $guarded = [];

    // 3. Relasi ke Pembeli (User)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // 4. Relasi ke Detail Barang (Order Items)
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

    // 5. Relasi ke Metode Pembayaran
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    // 6. Relasi ke Notifikasi User
    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class, 'order_id', 'order_id');
    }
}