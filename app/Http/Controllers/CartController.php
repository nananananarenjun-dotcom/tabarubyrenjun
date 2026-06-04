<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // 1. Menampilkan isi keranjang milik pembeli yang sedang login
    public function index(Request $request)
    {
        // Ambil data keranjang berdasarkan user_id, sekalian bawa data produknya
        $cartItems = Cart::with('product')
                        ->where('user_id', $request->user()->id)
                        ->get();

        return response()->json([
            'message' => 'Data keranjang berhasil diambil',
            'data' => $cartItems
        ], 200);
    }

    // 2. Menambah barang ke keranjang
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $userId = $request->user()->id;

        // Cek apakah barang ini sudah ada di keranjang user tersebut
        $existingCart = Cart::where('user_id', $userId)
                            ->where('product_id', $request->product_id)
                            ->first();

        if ($existingCart) {
            // Kalau sudah ada, tinggal tambahkan jumlahnya (quantity)
            $existingCart->increment('quantity', $request->quantity);
            $cart = $existingCart;
        } else {
            // Kalau belum ada, buat baris keranjang baru
            $cart = Cart::create([
                'user_id' => $userId,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity
            ]);
        }

        return response()->json([
            'message' => 'Produk berhasil ditambahkan ke keranjang',
            'data' => $cart
        ], 201);
    }

    // 3. Menghapus barang dari keranjang
    public function destroy(Request $request, $id)
    {
        // Cari item keranjang berdasarkan ID keranjang DAN ID user yang login (biar aman tidak dihapus orang lain)
        $cartItem = Cart::where('id', $id)
                        ->where('user_id', $request->user()->id)
                        ->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Item tidak ditemukan di keranjang'], 404);
        }

        $cartItem->delete();

        return response()->json([
            'message' => 'Produk dihapus dari keranjang'
        ], 200);
    }
}