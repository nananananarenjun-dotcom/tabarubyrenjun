<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    // 1. TAMBAHAN: Konfigurasi Primary Key String
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id', // 2. TAMBAHAN: Izinkan user_id untuk diisi
        'name',
        'email',
        'password',
        'phone',
        'address',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // 3. TAMBAHAN: Logika Otomatis Pembuat Kode USR001
    // Fungsi Otomatis Pembuat Kode ADM001 atau USR001
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // 1. Cek jabatannya, tentukan awalan hurufnya (Prefix)
            $prefix = ($model->role === 'admin') ? 'ADM' : 'USR';

            // 2. Cari data terakhir di database yang huruf awalnya sama (ADM saja atau USR saja)
            $lastData = self::where('user_id', 'like', $prefix . '%')
                            ->orderBy('user_id', 'desc')
                            ->first();

            // 3. Cetak nomor urutnya
            if (!$lastData) {
                $model->user_id = $prefix . '001';
            } else {
                $lastNumber = (int) substr($lastData->user_id, 3);
                $model->user_id = $prefix . sprintf('%03d', $lastNumber + 1);
            }
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;

        // Kalau nanti hanya admin yang boleh masuk Filament, ganti jadi:
        // return $this->role === 'admin';
    }

    // 4. TAMBAHAN: Penegasan nama kolom di relasi agar Laravel tidak kebingungan mencari kolom 'id'
    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class, 'user_id', 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'user_id');
    }

    public function trainingRegistrations()
    {
        return $this->hasMany(TrainingRegistration::class, 'user_id', 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id', 'user_id');
    }
}