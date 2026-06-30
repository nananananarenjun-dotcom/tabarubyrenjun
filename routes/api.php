<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\TrainingController; // <- Ini yang bikin error 500 tadi
use App\Http\Controllers\OrderController;    // <- Ini biar checkout tidak error
use App\Http\Controllers\PaymentController;
use App\Models\Courier;
use App\Models\Category;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Api\UserNotificationController;
use App\Http\Controllers\FinanceReportController;


// Route Publik (Bisa diakses tanpa harus login/tanpa token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'authenticate']);

Route::get('/products/{product_id}/reviews', [ReviewController::class, 'index']);


Route::get('/finance-report-pdf', [FinanceReportController::class, 'exportPdf'])
    ->name('finance.report.pdf');
    
// Route Publik untuk Katalog
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);

    Route::get('/categories', function () {
    return response()->json([
        'data' => Category::all()
    ]);
});

Route::post('/midtrans/callback', [PaymentController::class, 'callback']);

Route::get('/trainings', [TrainingController::class, 'index']);

// Route Terproteksi (Hanya bisa diakses jika React mengirimkan Token KTP)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    // Route untuk update profil
Route::put('/profile', [ProfileController::class, 'update']);

Route::get('/my-trainings', [TrainingController::class, 'myTrainings']);

// notifikasiuser
 Route::get('/user/notifications', [UserNotificationController::class, 'index']);
    Route::get('/user/notifications/unread-count', [UserNotificationController::class, 'unreadCount']);
    Route::patch('/user/notifications/{id}/read', [UserNotificationController::class, 'markAsRead']);
    Route::patch('/user/notifications/read-all', [UserNotificationController::class, 'markAllAsRead']);

Route::post('/reviews', [ReviewController::class, 'store']);
Route::get('/reviews/my-products', [ReviewController::class, 'myProducts']);
    
    // Route ini untuk React mengecek data profil pembeli yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Route Keranjang Belanja
    Route::get('/cart', [CartController::class, 'index']); // Lihat keranjang
    Route::post('/cart', [CartController::class, 'store']); // Tambah barang
    Route::delete('/cart/{id}', [CartController::class, 'destroy']); // Hapus barang

    // Route Pesanan (Checkout)
    Route::get('/orders', [OrderController::class, 'index']); // Riwayat belanja
    Route::post('/checkout', [OrderController::class, 'checkout']); // Proses checkout

    // Route Pembayaran
    Route::get('/payment-methods', [PaymentController::class, 'getMethods']);
    Route::post('/payments', [PaymentController::class, 'uploadProof']);
    Route::get('/couriers', function () {
    return response()->json([
        'data' => Courier::where('is_active', true)->get()
    ]);
});

    // (Taruh di dalam grup ini bersama keranjang dan checkout)
    Route::post('/trainings/register', [TrainingController::class, 'register']);
});