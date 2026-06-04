<?php

namespace App\Notifications;

use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;
use Illuminate\Notifications\Notification;

class OrderNotification extends Notification
{
    public function __construct(
        public string $message,
        public string $recordId,
        public string $status,
        public string $type = 'order', // order | training
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $title = $this->type === 'training'
            ? 'Pembayaran Pelatihan'
            : 'Pembayaran Produk';

        $icon = $this->type === 'training'
            ? 'heroicon-o-academic-cap'
            : 'heroicon-o-shopping-bag';

        $label = $this->type === 'training'
            ? 'Lihat Pelatihan'
            : 'Lihat Order';

        $url = $this->type === 'training'
            ? '/sabiraecoprint/training-registrations/' . $this->recordId . '/edit'
            : '/sabiraecoprint/orders/' . $this->recordId . '/edit';

        return FilamentNotification::make()
            ->title($title)
            ->body($this->message)
            ->icon($icon)
            ->iconColor('success')
            ->actions([
                Action::make('lihat')
                    ->label($label)
                    ->url($url)
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}