<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class RangkumanPenjualanProduk extends BaseWidget
{
    
    // Judul tabel widget di dashboard
    protected static ?string $heading = 'Rincian Kuantitas Produk Terjual (Harian & Mingguan)';

    // Mengatur lebar penuh agar enak dibaca di dashboard
    protected int | string | array $columnSpan = 'full';

    // Muncul di urutan ke-3 (di bawah kotak stats dan tabel detail pelanggan)
    protected static ?int $sort = 3;

    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model $record): string
    {
        // Membuat ID unik gabungan dari tanggal dan nama produk
        return $record->tanggal_jual . '-' . $record->nama_produk;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                /*
                  Logika: Kita mengambil data dari tabel order_items, 
                  lalu kita kelompokkan (Group By) berdasarkan tanggal dan produk.
                  Kita hanya menghitung pesanan yang sudah dibayar (paid, completed, shipped).
                */
                OrderItem::query()
                    ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
                    ->join('products', 'order_items.product_id', '=', 'products.product_id')
                    ->whereIn('orders.status', ['paid', 'completed', 'shipped'])
                    ->select([
                        DB::raw("DATE(orders.created_at) as tanggal_jual"),
                        'products.name as nama_produk',
                        DB::raw("SUM(order_items.quantity) as total_qty"),
                        DB::raw("SUM(order_items.quantity * order_items.price) as total_nominal")
                    ])
                    ->groupBy('tanggal_jual', 'products.name')
                    ->orderBy('tanggal_jual', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_jual')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_produk')
                    ->label('Nama Produk')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_qty')
                    ->label('Jumlah Terjual')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => $state . ' pcs'),

                Tables\Columns\TextColumn::make('total_nominal')
                    ->label('Total Pendapatan Produk')
                    ->money('IDR', locale: 'id'),

                // Kolom penanda otomatis apakah ini penjualan Hari Ini atau Minggu Ini
                Tables\Columns\TextColumn::make('periode')
                    ->label('Keterangan Periode')
                    ->getStateUsing(function ($record) {
                        $tgl = Carbon::parse($record->tanggal_jual);
                        $pilihan = [];
                        
                        if ($tgl->isToday()) {
                            $pilihan[] = 'Hari Ini';
                        }
                        if ($tgl->isCurrentWeek()) {
                            $pilihan[] = 'Minggu Ini';
                        }
                        if ($tgl->isCurrentMonth()) {
                            $pilihan[] = 'Bulan Ini';
                        }

                        return implode(' & ', $pilihan) ?: 'Periode Lalu';
                    })
                    ->badge()
                    ->color(fn ($state) => str_contains($state, 'Hari Ini') ? 'success' : 'primary'),
            ])
            ->filters([
                // Fitur canggih: Dosen/Manajer bisa memfilter tabel ini untuk hanya melihat data harian atau mingguan
                Tables\Filters\Filter::make('hari_ini')
                    ->label('Hanya Hari Ini')
                    ->query(fn ($query) => $query->whereDate('orders.created_at', Carbon::today())),

                Tables\Filters\Filter::make('minggu_ini')
                    ->label('Hanya Minggu Ini')
                    ->query(fn ($query) => $query->whereBetween('orders.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])),
                
                Tables\Filters\Filter::make('bulan_ini')
                    ->label('Hanya Bulan Ini')
                    ->query(fn ($query) => $query->whereMonth('orders.created_at', Carbon::now()->month)->whereYear('orders.created_at', Carbon::now()->year)),
            ]);
    }
}