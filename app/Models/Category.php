<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // 1. Konfigurasi Primary Key Custom
    protected $primaryKey = 'category_id';
    public $incrementing = false;
    protected $keyType = 'string';

    // 2. Izinkan semua kolom untuk diisi
    protected $guarded = [];

    // 3. Logika Otomatis Pembuat Kode CAT001
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $lastData = self::orderBy('category_id', 'desc')->first();

            if (!$lastData) {
                $model->category_id = 'CAT001';
            } else {
                $lastNumber = (int) substr($lastData->category_id, 3);
                $model->category_id = 'CAT' . sprintf('%03d', $lastNumber + 1);
            }
        });
    }

    // 4. Relasi ke Produk
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'category_id');
    }
}