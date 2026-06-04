<?php

namespace App\Notifications;

use App\Models\TrainingRegistration;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminTrainingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public TrainingRegistration $registration
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $packageTitle = $this->registration->trainingPackage?->title ?? 'Paket Pelatihan';

        return FilamentNotification::make()
            ->title('🎓 Pendaftaran Pelatihan Baru!')
            ->body($this->registration->user?->name . ' mendaftar pelatihan ' . $packageTitle . '.')
            ->success()
            ->actions([
                Action::make('view')
                    ->label('Lihat Pendaftaran')
                    ->url('/sabiraecoprint/training-registrations/' . $this->registration->id . '/edit'),
            ])
            ->getDatabaseMessage();
    }
}