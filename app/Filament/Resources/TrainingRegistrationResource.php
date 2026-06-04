<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainingRegistrationResource\Pages;
use App\Models\TrainingRegistration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TrainingRegistrationResource extends Resource
{
    protected static ?string $model = TrainingRegistration::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Training Registrations';

    protected static ?string $modelLabel = 'Training Registration';

    protected static ?string $pluralModelLabel = 'Training Registrations';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Participant Detail')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Participant')
                            ->relationship('user', 'name')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('training_package_id')
                            ->label('Training Package')
                            ->relationship('trainingPackage', 'title')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('participant_count')
                            ->label('Participant Count')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('total_price')
                            ->label('Total Price')
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Schedule Detail')
                    ->description('Check and confirm the requested training schedule.')
                    ->schema([
                        Forms\Components\DatePicker::make('scheduled_date')
                            ->label('Scheduled Date')
                            ->required(),

                        Forms\Components\TimePicker::make('scheduled_time')
                            ->label('Scheduled Time')
                            ->seconds(false),

                        Forms\Components\Textarea::make('location')
                            ->label('Location')
                            ->placeholder('Training location')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_custom_schedule')
                            ->label('Custom Schedule Request')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending Review',
                                'approved_by_admin' => 'Approved by Admin',
                                'paid' => 'Paid',
                                'completed' => 'Completed',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('User Notes & Payment')
                    ->schema([
                        Forms\Components\Textarea::make('user_notes')
                            ->label('User Notes')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),

                        FileUpload::make('payment_proof')
                            ->label('Payment Proof')
                            ->image()
                            ->disk('public')
                            ->directory('uploads')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Participant')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('trainingPackage.title')
                    ->label('Package')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('trainingPackage.type')
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
                    }),

                Tables\Columns\TextColumn::make('trainingPackage.delivery_mode')
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
                    }),

                Tables\Columns\TextColumn::make('scheduled_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('scheduled_time')
                    ->label('Time')
                    ->time('H:i')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->limit(25)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('participant_count')
                    ->label('Participants')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('payment_proof')
                    ->label('Payment Proof')
                    ->disk('public')
                    ->square()
                    ->size(70),

                Tables\Columns\IconColumn::make('is_custom_schedule')
                    ->label('Custom?')
                    ->boolean(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved_by_admin' => 'primary',
                        'paid' => 'success',
                        'completed' => 'gray',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending Review',
                        'approved_by_admin' => 'Approved by Admin',
                        'paid' => 'Paid',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('delivery_mode')
                    ->label('Delivery Mode')
                    ->relationship('trainingPackage', 'delivery_mode')
                    ->options([
                        'offline' => 'Offline',
                        'online' => 'Online',
                    ]),

                Tables\Filters\TernaryFilter::make('is_custom_schedule')
                    ->label('Custom Schedule'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrainingRegistrations::route('/'),
            'create' => Pages\CreateTrainingRegistration::route('/create'),
            'edit' => Pages\EditTrainingRegistration::route('/{record}/edit'),
        ];
    }
}