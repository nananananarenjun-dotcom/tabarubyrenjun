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

    protected $fillable = [
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

    public function canAccessPanel(Panel $panel): bool
    {
        return true;

        // Kalau nanti hanya admin yang boleh masuk Filament, ganti jadi:
        // return $this->role === 'admin';
    }

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function trainingRegistrations()
    {
        return $this->hasMany(TrainingRegistration::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}