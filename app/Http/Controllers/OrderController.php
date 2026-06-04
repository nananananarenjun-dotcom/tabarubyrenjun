<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Notifications\AdminOrderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        try {
            $orders = Order::with('items.product')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $trainings = \App\Models\TrainingRegistration::with('trainingPackage')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'message' => 'Berhasil mengambil riwayat pesanan',
                'data' => [
                    'orders' => $orders,
                    'trainings' => $trainings,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error Server: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string',
            'courier_name' => 'required|string',
            'shipping_cost' => 'required|numeric',
            'selected_items' => 'required|array',
            'selected_items.*' => 'integer|exists:carts,id',
        ]);

        $user = $request->user();

        $cartItems = Cart::with('product')
            ->where('user_id', $user->id)
            ->whereIn('id', $request->selected_items)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Barang yang dipilih tidak ditemukan',
            ], 400);
        }

        DB::beginTransaction();

        try {
            $totalProduk = 0;

            foreach ($cartItems as $item) {
                $totalProduk += $item->product->price * $item->quantity;
            }

            $grandTotal = $totalProduk + $request->shipping_cost;
            $invoiceNumber = 'INV-' . time() . '-' . $user->id;

            $order = Order::create([
                'user_id' => $user->id,
                'invoice_number' => $invoiceNumber,
                'total_price' => $grandTotal,
                'status' => 'pending',
                'shipping_address' => $request->shipping_address,
                'courier_name' => $request->courier_name,
            ]);

            foreach ($cartItems as $item) {
                $product = \App\Models\Product::where('id', $item->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($product->status !== 'available') {
                    throw new \Exception('Produk ' . $product->name . ' sedang tidak tersedia.');
                }

                if ($product->stock < $item->quantity) {
                    throw new \Exception(
                        'Stok produk ' . $product->name . ' tidak mencukupi. Sisa stok: ' . $product->stock
                    );
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'price' => $product->price,
                ]);

                $product->stock = $product->stock - $item->quantity;

                if ($product->stock <= 0) {
                    $product->stock = 0;
                    $product->status = 'sold_out';
                }

                $product->save();
            }

            Cart::where('user_id', $user->id)
                ->whereIn('id', $request->selected_items)
                ->delete();

            // KONFIGURASI MIDTRANS
            Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
            Config::$isSanitized = true;
            Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => $invoiceNumber,
                    'gross_amount' => $grandTotal,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '0800000000',
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            $order->snap_token = $snapToken;
            $order->save();

            DB::commit();

            // NOTIFIKASI ADMIN FILAMENT
            // Ini dibuat setelah checkout berhasil, jadi kalau notif gagal order tetap aman.
            try {
                $admin = User::find(1);

                if ($admin) {
                    $admin->notify(new AdminOrderNotification($order));
                }
            } catch (\Throwable $notificationError) {
                Log::error('Gagal membuat notifikasi order admin', [
                    'order_id' => $order->id,
                    'message' => $notificationError->getMessage(),
                ]);
            }

            $order->load('items.product');

            return response()->json([
                'message' => 'Checkout berhasil',
                'snap_token' => $snapToken,
                'data' => $order,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}