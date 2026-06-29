<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TrainingParticipantsTable extends BaseWidget
{
    protected static ?int $sort = 10;
    protected static ?string $heading = 'Peserta Pelatihan';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->whereHas('trainingRegistrations')
                    ->with([
                        'trainingRegistrations.trainingPackage'
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email'),

                Tables\Columns\TextColumn::make('total_peserta')
                    ->label('Jumlah Peserta')
                    ->getStateUsing(fn ($record) =>
                        $record->trainingRegistrations->sum('participant_count')
                    ),

                Tables\Columns\TextColumn::make('pelatihan')
                    ->label('Pelatihan')
                    ->getStateUsing(fn ($record) =>
                        $record->trainingRegistrations
                            ->map(fn ($item) => $item->trainingPackage?->title)
                            ->filter()
                            ->unique()
                            ->implode(', ')
                    )
                    ->wrap(),
            ]);
    }
}