<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Mengambil semua kategori
    public function index()
    {
        $categories = Category::all();
        
        return response()->json([
            'message' => 'Berhasil mengambil data kategori',
            'data' => $categories
        ], 200);
    }
}