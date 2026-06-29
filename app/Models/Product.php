<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // Konfigurasi Primary Key Custom
    protected $primaryKey = 'product_id';
    public $incrementing = false;
    protected $keyType = 'string';

    // Izinkan semua kolom (termasuk image) untuk diisi
    protected $guarded = []; 

    // Relasi ke Category (Opsional tapi penting untuk Filament)
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    // Fungsi Otomatis Pembuat Kode PRD001
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $lastData = self::orderBy('product_id', 'desc')->first();

            if (!$lastData) {
                $model->product_id = 'PRD001';
            } else {
                $lastNumber = (int) substr($lastData->product_id, 3);
                $model->product_id = 'PRD' . sprintf('%03d', $lastNumber + 1);
            }
        });
    }
}