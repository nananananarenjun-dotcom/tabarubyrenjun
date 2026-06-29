<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LaporanTransaksiDetail extends BaseWidget
{
    // Mengatur judul tabel
    protected static ?string $heading = 'Rincian Penjualan & Pembelian Pelanggan Terbaru';

    // Mengatur agar tabel ini lebarnya penuh (Full width)
    protected int | string | array $columnSpan = 'full';

    // Mengatur agar tabel ini muncul di urutan ke-2 (di bawah kotak Stats/Summary)
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Mengambil data Order terbaru, sekalian menarik data relasi User dan Item Produknya agar database tidak berat
                Order::query()->with(['user', 'orderItems.product'])->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Transaksi')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_id')
                    ->label('No. Pesanan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Pelanggan')
                    ->searchable(),

                // INI ADALAH LOGIKA UNTUK MEMUNCULKAN "Pelanggan A beli Produk X (2 pcs), Produk Y (1 pcs)"
                Tables\Columns\TextColumn::make('rincian_produk')
                    ->label('Detail Produk yang Dibeli')
                    ->getStateUsing(function (Order $record) {
                        // Menggabungkan semua barang yang dibeli dalam 1 transaksi menjadi kalimat
                        return $record->orderItems->map(function ($item) {
                            // Cek jika produknya ada (mencegah error jika produk terhapus)
                            $namaProduk = $item->product ? $item->product->name : 'Produk Terhapus';
                            return $namaProduk . ' (' . $item->quantity . ' pcs)';
                        })->implode(', ');
                    })
                    ->wrap() // Agar teksnya turun ke bawah kalau kepanjangan
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Bayar')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid', 'completed', 'shipped' => 'success',
                        'processing' => 'info',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ]);
    }
}