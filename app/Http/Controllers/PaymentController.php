<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\TrainingRegistration;
use App\Models\PaymentMethod;
use App\Models\User;
/* use Filament\Notifications\Notification; 
use Filament\Notifications\Actions\Action;  */
use App\Notifications\OrderNotification;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function getMethods()
    {
        $methods = PaymentMethod::where('is_active', true)->get(); 
        return response()->json([
            'message' => 'Daftar metode pembayaran',
            'data' => $methods
        ], 200);
    }

    public function uploadProof(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'type' => 'nullable|string|in:order,training'
        ]);

        $type = $request->type ?? 'order';
        $imagePath = $request->file('payment_proof')->store('payments', 'public');

        if ($type === 'training') {
            $record = TrainingRegistration::where('id', $request->order_id)->where('user_id', $request->user()->id)->first();
            if (!$record) return response()->json(['message' => 'Pendaftaran pelatihan tidak ditemukan'], 404);
            $record->update(['payment_method_id' => $request->payment_method_id, 'payment_proof' => $imagePath]);
        } else {
            $record = Order::where('id', $request->order_id)->where('user_id', $request->user()->id)->first();
            if (!$record) return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
            $record->update(['payment_method_id' => $request->payment_method_id, 'payment_proof' => $imagePath]);
        }

        return response()->json(['message' => 'Bukti pembayaran berhasil diupload!'], 200);
    }

    // ========================================================
    // FUNGSI CALLBACK WEBHOOK MIDTRANS
    // ========================================================
public function callback(Request $request)
{
    \Log::info('MIDTRANS DATANG!', $request->all());

    $transactionStatus = $request->transaction_status;
    $orderId = $request->order_id;

    if (!in_array($transactionStatus, ['settlement', 'capture'])) {
        return response()->json(['message' => 'Status transaksi belum final'], 200);
    }

    $admins = User::where('role', 'admin')->get();

    // =========================
    // 1. Cek pembayaran produk
    // =========================
    $order = Order::where('invoice_number', $orderId)->first();

    if (!$order && is_numeric($orderId)) {
        $order = Order::where('id', $orderId)->first();
    }

    if ($order) {
        \Log::info('ORDER DITEMUKAN', [
            'order_id_midtrans' => $orderId,
            'order_db_id' => $order->id,
            'invoice_number' => $order->invoice_number,
            'status_midtrans' => $transactionStatus,
        ]);

        if ($order->status !== 'paid') {
            $order->update(['status' => 'paid']);

            foreach ($admins as $admin) {
                $admin->notify(new OrderNotification(
                    "Hore! Pesanan {$order->invoice_number} berhasil dibayar lunas!",
                    (string) $order->id,
                    'paid',
                    'order'
                ));
            }

            \Log::info('NOTIF ORDER BERHASIL DISIMPAN', [
                'jumlah_admin' => $admins->count(),
                'order_id' => $order->id,
            ]);
        }

        return response()->json(['message' => 'Callback Produk Berhasil Diproses'], 200);
    }

    // =============================
    // 2. Cek pembayaran pelatihan
    // =============================
    $trainingId = $orderId;

    if (str_starts_with($orderId, 'TRX-TRAIN-')) {
        $trainingId = str_replace('TRX-TRAIN-', '', $orderId);
    }

    if (is_numeric($trainingId)) {
        $training = TrainingRegistration::find($trainingId);

        if ($training) {
            \Log::info('TRAINING DITEMUKAN', [
                'order_id_midtrans' => $orderId,
                'training_db_id' => $training->id,
                'status_midtrans' => $transactionStatus,
            ]);

            if ($training->status !== 'paid') {
                $training->update(['status' => 'paid']);

                foreach ($admins as $admin) {
                    $admin->notify(new OrderNotification(
                        "Pendaftaran pelatihan #{$training->id} berhasil dibayar lunas!",
                        (string) $training->id,
                        'paid',
                        'training'
                    ));
                }

                \Log::info('NOTIF TRAINING BERHASIL DISIMPAN', [
                    'jumlah_admin' => $admins->count(),
                    'training_id' => $training->id,
                ]);
            }

            return response()->json(['message' => 'Callback Pelatihan Berhasil Diproses'], 200);
        }
    }

    return response()->json(['message' => 'Pesanan tidak ditemukan di sistem kami'], 404);
}
}