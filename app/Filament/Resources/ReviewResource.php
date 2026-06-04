<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Reviews';

    protected static ?string $pluralModelLabel = 'Reviews';

    protected static ?string $navigationGroup = 'Customer Management';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Review dari Pengguna')
                    ->schema([
                        Forms\Components\Placeholder::make('user_name')
                            ->label('Nama User')
                            ->content(fn (?Review $record): string => $record?->user?->name ?? '-'),

                        Forms\Components\Placeholder::make('product_name')
                            ->label('Produk')
                            ->content(fn (?Review $record): string => $record?->product?->name ?? '-'),

                        Forms\Components\Placeholder::make('rating_display')
                            ->label('Rating')
                            ->content(fn (?Review $record): string => $record ? $record->rating . ' / 5' : '-'),

                        Forms\Components\Textarea::make('comment')
                            ->label('Komentar User')
                            ->disabled()
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('photo')
                            ->label('Foto Review')
                            ->disk('public')
                            ->image()
                            ->disabled()
                            ->openable()
                            ->downloadable(),

                        Forms\Components\FileUpload::make('video')
                            ->label('Video Review')
                            ->disk('public')
                            ->disabled()
                            ->openable()
                            ->downloadable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Balasan Admin')
                    ->description('Isi balasan untuk menanggapi review pengguna. Balasan ini akan tampil di halaman detail produk.')
                    ->schema([
                        Forms\Components\Textarea::make('admin_reply')
                            ->label('Balasan')
                            ->placeholder('Contoh: Terima kasih atas ulasannya. Semoga produk Sabira nyaman digunakan.')
                            ->rows(5)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('admin_replied_at_display')
                            ->label('Terakhir Dibalas')
                            ->content(function (?Review $record): string {
                                if (!$record?->admin_replied_at) {
                                    return 'Belum dibalas';
                                }

                                return $record->admin_replied_at->format('d M Y, H:i');
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->limit(28),

                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state . ' / 5')
                    ->color(fn ($state): string => match ((int) $state) {
                        5 => 'success',
                        4 => 'info',
                        3 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Komentar')
                    ->limit(40)
                    ->placeholder('-'),

                Tables\Columns\IconColumn::make('admin_reply')
                    ->label('Dibalas')
                    ->boolean()
                    ->getStateUsing(fn (Review $record): bool => filled($record->admin_reply)),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Review')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rating')
                    ->label('Filter Rating')
                    ->options([
                        5 => '5 Stars',
                        4 => '4 Stars',
                        3 => '3 Stars',
                        2 => '2 Stars',
                        1 => '1 Star',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Balas Review'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}