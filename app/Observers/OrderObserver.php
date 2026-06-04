<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\UserNotification;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            $this->createOrderStatusNotification($order);
        }

        if ($order->wasChanged('status_pengiriman')) {
            $this->createShippingStatusNotification($order);
        }

        if ($order->wasChanged('no_resi') && !empty($order->no_resi)) {
            $this->createResiNotification($order);
        }
    }

    private function createOrderStatusNotification(Order $order): void
    {
        $status = strtolower($order->status);

        $title = match ($status) {
            'pending' => 'Pesanan Berhasil Dibuat',
            'paid', 'settlement', 'success' => 'Pembayaran Berhasil',
            'processing', 'diproses' => 'Pesanan Sedang Diproses',
            'shipped', 'dikirim' => 'Pesanan Sedang Dikirim',
            'completed', 'diterima', 'selesai' => 'Pesanan Selesai',
            'cancelled', 'dibatalkan' => 'Pesanan Dibatalkan',
            default => 'Status Pesanan Diperbarui',
        };

        $message = match ($status) {
            'pending' => 'Pesanan kamu berhasil dibuat dan menunggu pembayaran.',
            'paid', 'settlement', 'success' => 'Pembayaran kamu sudah berhasil dikonfirmasi.',
            'processing', 'diproses' => 'Pesanan kamu sedang diproses oleh admin.',
            'shipped', 'dikirim' => 'Pesanan kamu sedang dalam proses pengiriman.',
            'completed', 'diterima', 'selesai' => 'Pesanan kamu telah selesai. Terima kasih sudah berbelanja.',
            'cancelled', 'dibatalkan' => 'Pesanan kamu telah dibatalkan.',
            default => 'Status pesanan kamu telah diperbarui.',
        };

        UserNotification::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'type' => 'order_status',
            'title' => $title,
            'message' => $message,
            'data' => [
                'order_id' => $order->id,
                'invoice_number' => $order->invoice_number ?? null,
                'status' => $order->status,
            ],
        ]);
    }

    private function createShippingStatusNotification(Order $order): void
    {
        $statusPengiriman = strtolower($order->status_pengiriman);

        $title = match ($statusPengiriman) {
            'dikemas' => 'Pesanan Sedang Dikemas',
            'dikirim' => 'Pesanan Sedang Dikirim',
            'diterima' => 'Pesanan Telah Diterima',
            default => 'Status Pengiriman Diperbarui',
        };

        $message = match ($statusPengiriman) {
            'dikemas' => 'Pesanan kamu sedang dikemas oleh admin.',
            'dikirim' => 'Pesanan kamu sudah dikirim. Silakan cek detail pesanan untuk melihat informasi pengiriman.',
            'diterima' => 'Pesanan kamu telah diterima. Kamu bisa memberikan ulasan produk.',
            default => 'Status pengiriman pesanan kamu telah diperbarui.',
        };

        UserNotification::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'type' => 'shipping_status',
            'title' => $title,
            'message' => $message,
            'data' => [
                'order_id' => $order->id,
                'invoice_number' => $order->invoice_number ?? null,
                'status_pengiriman' => $order->status_pengiriman,
                'no_resi' => $order->no_resi ?? null,
            ],
        ]);
    }

    private function createResiNotification(Order $order): void
    {
        UserNotification::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'type' => 'resi',
            'title' => 'Nomor Resi Tersedia',
            'message' => 'Nomor resi pesanan kamu sudah tersedia: ' . $order->no_resi,
            'data' => [
                'order_id' => $order->id,
                'invoice_number' => $order->invoice_number ?? null,
                'no_resi' => $order->no_resi,
            ],
        ]);
    }
}