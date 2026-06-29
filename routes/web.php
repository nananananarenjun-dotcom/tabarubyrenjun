<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'Backend API Galeri Sabira Aktif!']);
});

Route::get('/test-signature', function () {
    return response()->json([
        'app_url' => config('app.url'),
        'current_url' => request()->fullUrl(),
        'https' => request()->secure(),
        'host' => request()->getHost(),
    ]);
});

Route::get('/setup-data', function () {
    // 1. Ratakan database sampai bersih
    \Illuminate\Support\Facades\DB::statement('TRUNCATE TABLE order_items, orders, products, categories, users CASCADE;');

    // 2. Bikin Akun
    \Illuminate\Support\Facades\DB::table('users')->insert([
        ['user_id' => 'ADM001', 'name' => 'Admin Sabira', 'email' => 'sabira@gmail.com', 'password' => bcrypt('rahasia'), 'role' => 'admin', 'phone' => null, 'created_at' => now(), 'updated_at' => now()],
        ['user_id' => 'USR001', 'name' => 'Cornel Customer', 'email' => 'cornel@gmail.com', 'password' => bcrypt('rahasia'), 'role' => 'customer', 'phone' => '085290008285', 'created_at' => now(), 'updated_at' => now()]
    ]);

    // 3. Bikin Kategori
    \Illuminate\Support\Facades\DB::table('categories')->insert([
        ['category_id' => 'CAT001', 'name' => 'Ecoprint Pakaian', 'slug' => 'ecoprint-pakaian', 'created_at' => now(), 'updated_at' => now()]
    ]);

    // 4. Bikin Produk Peluang
    \Illuminate\Support\Facades\DB::table('products')->insert([
        ['product_id' => 'PRD001', 'category_id' => 'CAT001', 'name' => 'Ide Jaket Parasut', 'slug' => 'ide-jaket', 'description' => 'Baru wacana', 'price' => 300000, 'stock' => 0, 'status' => 'Belum Terealisasi', 'created_at' => now(), 'updated_at' => now()],
        ['product_id' => 'PRD002', 'category_id' => 'CAT001', 'name' => 'Ide Kemeja Flanel', 'slug' => 'ide-kemeja', 'description' => 'Baru wacana', 'price' => 150000, 'stock' => 0, 'status' => 'Belum Terealisasi', 'created_at' => now(), 'updated_at' => now()]
    ]);

    // 5. Bikin Nota Pesanan
    \Illuminate\Support\Facades\DB::table('orders')->insert([
        ['order_id' => 'TRX001', 'user_id' => 'USR001', 'invoice_number' => 'INV-001', 'total_price' => 300000, 'status' => 'completed', 'shipping_address' => 'Sleman', 'created_at' => now()->subMonths(2), 'updated_at' => now()->subMonths(2)],
        ['order_id' => 'TRX002', 'user_id' => 'USR001', 'invoice_number' => 'INV-002', 'total_price' => 300000, 'status' => 'paid', 'shipping_address' => 'Bantul', 'created_at' => now()->subMonth(), 'updated_at' => now()->subMonth()],
        ['order_id' => 'TRX003', 'user_id' => 'USR001', 'invoice_number' => 'INV-003', 'total_price' => 600000, 'status' => 'paid', 'shipping_address' => 'Malioboro', 'created_at' => now(), 'updated_at' => now()],
    ]);

    // 6. Bikin Detail Barang
    \Illuminate\Support\Facades\DB::table('order_items')->insert([
        ['order_id' => 'TRX001', 'product_id' => 'PRD001', 'quantity' => 1, 'price' => 300000, 'created_at' => now()->subMonths(2), 'updated_at' => now()->subMonths(2)],
        ['order_id' => 'TRX002', 'product_id' => 'PRD002', 'quantity' => 2, 'price' => 150000, 'created_at' => now()->subMonth(), 'updated_at' => now()->subMonth()],
        ['order_id' => 'TRX003', 'product_id' => 'PRD001', 'quantity' => 2, 'price' => 300000, 'created_at' => now(), 'updated_at' => now()],
    ]);

    return "<h1>BERHASIL! DATA SUDAH MASUK SEMUA! SILAKAN BUKA ADMIN!</h1>";
});