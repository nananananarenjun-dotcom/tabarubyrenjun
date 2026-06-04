<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\FileUpload;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    // Ganti icon agar lebih merepresentasikan "Pembayaran/Rekening"
    protected static ?string $navigationIcon = 'heroicon-o-credit-card'; 
    
    // Ganti nama menu di sidebar
    protected static ?string $navigationLabel = 'Rekening & QRIS';
    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Rekening Pembayaran')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->label('Nama Bank / E-Wallet')
                            ->placeholder('Contoh: BCA, Mandiri, OVO, QRIS')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('account_number')
                            ->label('Nomor Rekening')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('account_name')
                            ->label('Atas Nama')
                            ->required()
                            ->maxLength(255),
                            // ----- TAMBAHKAN INI UNTUK QRIS -----
        FileUpload::make('qr_code')
            ->label('Upload Gambar QRIS (Opsional)')
            ->image()
            ->directory('payment-methods')
            ->helperText('Kosongkan jika ini bukan metode QRIS.'),
        // ------------------------------------
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktifkan Metode Ini?')
                            ->default(true),
                    ])->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank/E-Wallet')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_number')
                    ->label('No. Rekening')
                    ->copyable() // Bisa di-copy langsung oleh admin
                    ->searchable(),
                Tables\Columns\TextColumn::make('account_name')
                    ->label('Atas Nama'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}