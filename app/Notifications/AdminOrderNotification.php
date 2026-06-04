<?php

namespace App\Notifications;

use App\Models\Order;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminOrderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('🛒 Pesanan Baru Masuk!')
            ->body('Invoice ' . $this->order->invoice_number . ' menunggu pembayaran VA.')
            ->success()
            ->actions([
                Action::make('view')
                    ->label('Lihat Order')
                    ->url('/sabiraecoprint/orders/' . $this->order->id . '/edit'),
            ])
            ->getDatabaseMessage();
    }
}