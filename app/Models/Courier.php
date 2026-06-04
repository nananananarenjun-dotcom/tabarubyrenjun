<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    // Mengizinkan semua kolom diisi (mass assignment)
    protected $guarded = ['id'];
}