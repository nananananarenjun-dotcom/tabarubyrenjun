<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Support\RawJs;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Products';

    protected static ?string $modelLabel = 'Product';

    protected static ?string $pluralModelLabel = 'Products';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Choose the product category. You do not need to type the category ID manually.'),

                Forms\Components\TextInput::make('name')
                    ->label('Product Name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set) {
                        $set('slug', Str::slug($state));
                    }),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Slug is used for product URL, for example: earth-tone-pashmina-scarf.'),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('price')
                    ->label('Harga')
                    ->required()
                    ->prefix('Rp')
                    ->mask(RawJs::make('$money($input, \'.\', \',\', 0)'))
                    ->dehydrateStateUsing(fn ($state) => (int) preg_replace('/[^0-9]/', '', $state))
                    ->placeholder('Contoh: 1499000'),

                Forms\Components\TextInput::make('stock')
                    ->label('Stok')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(1)
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        // PERBAIKAN 1: Jangan ubah status kalau produknya memang "Belum Terealisasi"
                        if ($get('status') !== 'Belum Terealisasi') {
                            if ((int) $state <= 0) {
                                $set('status', 'Habis');
                            } else {
                                $set('status', 'Tersedia');
                            }
                        }
                    }),

                Forms\Components\FileUpload::make('image')
                    ->label('Foto Utama')
                    ->image()
                    ->directory('products')
                    ->columnSpanFull(),

                // PERBAIKAN 2: Pilihan Status di Form Input
                Forms\Components\Select::make('status')
                    ->label('Status Realisasi Produk')
                    ->options([
                        'Tersedia' => 'Tersedia (Sudah Direalisasikan)',
                        'Belum Terealisasi' => 'Belum Terealisasi (Masih Rencana)',
                        'Habis' => 'Stok Habis',
                        'Arsip' => 'Diarsipkan',
                    ])
                    ->required()
                    ->default('Tersedia'),

                // (Bagian Galeri dihapus sementara atau biarkan jika kamu masih butuh)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->square(),

                // PERBAIKAN 3: Warna label status di tabel
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Tersedia' => 'success',
                        'Belum Terealisasi' => 'warning',
                        'Habis' => 'danger',
                        'Arsip' => 'gray',
                        default => 'gray',
                    })
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Category')
                    ->relationship('category', 'name'),

                // PERBAIKAN 4: Pilihan filter status di pojok kanan atas tabel
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Tersedia' => 'Tersedia',
                        'Belum Terealisasi' => 'Belum Terealisasi',
                        'Habis' => 'Habis',
                        'Arsip' => 'Arsip',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}