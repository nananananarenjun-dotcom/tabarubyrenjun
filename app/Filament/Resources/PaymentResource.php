<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action; // Untuk tombol Action
use Filament\Notifications\Notification; // Untuk munculin notifikasi sukses/error

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('payable_type')
                ->label('Tipe')
                ->formatStateUsing(fn ($state) => str_contains($state, 'Order') ? 'Produk' : 'Pelatihan'),
            
            // ----- UBAH BAGIAN INI -----
            Tables\Columns\ImageColumn::make('payment_proof')
                ->label('Bukti Transfer')
                ->square()
                ->size(80) // Ukuran gambar di tabel dibesarkan
                ->extraImgAttributes(['loading' => 'lazy'])
                // Logic supaya kalau gambar diklik, buka ukuran penuh di tab baru
                ->url(fn ($record) => asset('storage/' . $record->payment_proof))
                ->openUrlInNewTab(),
            // ---------------------------

            Tables\Columns\TextColumn::make('amount')->money('IDR', locale: 'id'),
            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'warning' => 'pending',
                    'success' => 'verified',
                    'danger' => 'rejected',
                ]),
        ])
        ->actions([
    // TOMBOL VERIFIKASI
    Tables\Actions\Action::make('verify')
        ->label('Terima')
        ->action(function ($record) {
            $record->update(['status' => 'verified']);
            $record->payable->update(['status' => 'paid']);

            // PERHATIKAN TANDA PANAH DI SINI
            Notification::make()->title('Pembayaran Diterima')->success()->send();
        })
        ->requiresConfirmation()
        ->color('success')
        ->icon('heroicon-o-check-circle')
        ->visible(fn ($record) => $record->status === 'pending'),

    // TOMBOL TOLAK
    Tables\Actions\Action::make('reject')
        ->label('Tolak')
        ->action(function ($record) {
            $record->update(['status' => 'rejected']);
            $record->payable->update(['status' => 'pending']);

            // PERHATIKAN TANDA PANAH DI SINI
            Notification::make()->title('Pembayaran Ditolak')->danger()->send();
        })
        ->requiresConfirmation()
        ->color('danger')
        ->icon('heroicon-o-x-circle')
        ->visible(fn ($record) => $record->status === 'pending'),
]);
}
public static function getPages(): array
{
    return [
        'index' => Pages\ListPayments::route('/'),
        // Kalau kamu cuma mau halaman daftar, baris index ini saja sudah cukup
    ];
}
}