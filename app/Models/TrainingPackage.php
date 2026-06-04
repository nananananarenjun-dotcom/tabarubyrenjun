<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingPackage extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
    'price' => 'decimal:2',
    'min_participants' => 'integer',
    'max_quota_per_session' => 'integer',
    'is_active' => 'boolean',
    'regular_date' => 'date',
    'regular_time' => 'datetime:H:i',
    'delivery_mode' => 'string',
];

    public function registrations(): HasMany
    {
        return $this->hasMany(TrainingRegistration::class);
    }
}