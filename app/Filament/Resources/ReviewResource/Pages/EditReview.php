<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use Filament\Resources\Pages\EditRecord;

class EditReview extends EditRecord
{
    protected static string $resource = ReviewResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['admin_reply'])) {
            $data['admin_replied_at'] = now();
        } else {
            $data['admin_replied_at'] = null;
        }

        return $data;
    }
}