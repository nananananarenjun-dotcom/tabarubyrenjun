<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainingPackageResource\Pages;
use App\Models\TrainingPackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class TrainingPackageResource extends Resource
{
    protected static ?string $model = TrainingPackage::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Training Packages';

    protected static ?string $modelLabel = 'Training Package';

    protected static ?string $pluralModelLabel = 'Training Packages';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Package Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Package Title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('type')
                            ->label('Package Type')
                            ->options([
                                'regular' => 'Regular Workshop',
                                'custom' => 'Custom Workshop',
                            ])
                            ->default('regular')
                            ->live()
                            ->required()
                            ->helperText('Regular has a fixed schedule. Custom schedule is chosen by the user.'),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('price')
                            ->label('Price')
                            ->required()
                            ->prefix('Rp')
                            ->mask(RawJs::make('$money($input, \'.\', \',\', 0)'))
                            ->dehydrateStateUsing(fn ($state) => (int) preg_replace('/[^0-9]/', '', $state))
                            ->placeholder('Example: 1499000'),

                        Forms\Components\TextInput::make('min_participants')
                            ->label('Minimum Participants')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),

                        Forms\Components\TextInput::make('max_quota_per_session')
                            ->label('Maximum Quota Per Session')
                            ->required()
                            ->numeric()
                            ->minValue(1),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Regular Workshop Schedule')
                    ->description('Only fill this section for regular workshop packages.')
                    ->schema([
                        Forms\Components\DatePicker::make('regular_date')
                            ->label('Regular Date')
                            ->native(false)
                            ->required(fn (Get $get): bool => $get('type') === 'regular'),

                        Forms\Components\TimePicker::make('regular_time')
                            ->label('Regular Time')
                            ->seconds(false)
                            ->required(fn (Get $get): bool => $get('type') === 'regular'),

                        Forms\Components\Textarea::make('location')
                            ->label('Regular Location')
                            ->placeholder('Example: Sabira Ecoprint Gallery')
                            ->required(fn (Get $get): bool => $get('type') === 'regular')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get): bool => $get('type') === 'regular'),

                Forms\Components\Section::make('Access After Acceptance')
                    ->description('This information will be shown to users after their registration is approved or paid.')
                    ->schema([
                        Forms\Components\Select::make('delivery_mode')
                            ->label('Training Delivery Mode')
                            ->options([
                                'offline' => 'Offline',
                                'online' => 'Online',
                            ])
                            ->default('offline')
                            ->live()
                            ->required()
                            ->helperText('Choose whether this training is held offline or online.'),

                        Forms\Components\TextInput::make('group_link')
                            ->label('WhatsApp / Telegram Group Link')
                            ->placeholder('Example: https://chat.whatsapp.com/...')
                            ->url()
                            ->nullable()
                            ->visible(fn (Get $get): bool => $get('delivery_mode') === 'offline')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('meeting_link')
                            ->label('Online Meeting Link')
                            ->placeholder('Example: https://meet.google.com/... or Zoom link')
                            ->url()
                            ->nullable()
                            ->visible(fn (Get $get): bool => $get('delivery_mode') === 'online')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('material_link')
                            ->label('Material Link')
                            ->placeholder('Example: Google Drive / PDF / video material link')
                            ->url()
                            ->nullable()
                            ->visible(fn (Get $get): bool => $get('delivery_mode') === 'online')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('after_acceptance_note')
                            ->label('Note After Acceptance')
                            ->placeholder('Example: Please join the group after your registration is accepted.')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Package')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'regular' => 'Regular',
                        'custom' => 'Custom',
                        default => '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'regular' => 'success',
                        'custom' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('delivery_mode')
                    ->label('Mode')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'offline' => 'Offline',
                        'online' => 'Online',
                        default => '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'offline' => 'success',
                        'online' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('min_participants')
                    ->label('Min')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_quota_per_session')
                    ->label('Max Quota')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('schedule')
                    ->label('Schedule')
                    ->state(function (TrainingPackage $record): string {
                        if ($record->type !== 'regular') {
                            return 'Custom by request';
                        }

                        $date = $record->regular_date
                            ? $record->regular_date->format('d M Y')
                            : '-';

                        $time = $record->regular_time
                            ? $record->regular_time->format('H:i')
                            : '-';

                        return "{$date}, {$time}";
                    }),

                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Package Type')
                    ->options([
                        'regular' => 'Regular Workshop',
                        'custom' => 'Custom Workshop',
                    ]),

                Tables\Filters\SelectFilter::make('delivery_mode')
                    ->label('Delivery Mode')
                    ->options([
                        'offline' => 'Offline',
                        'online' => 'Online',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrainingPackages::route('/'),
            'create' => Pages\CreateTrainingPackage::route('/create'),
            'edit' => Pages\EditTrainingPackage::route('/{record}/edit'),
        ];
    }
}