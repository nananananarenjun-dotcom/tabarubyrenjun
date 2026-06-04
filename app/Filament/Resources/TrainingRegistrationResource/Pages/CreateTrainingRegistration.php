<?php

namespace App\Filament\Resources\TrainingRegistrationResource\Pages;

use App\Filament\Resources\TrainingRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTrainingRegistration extends CreateRecord
{
    protected static string $resource = TrainingRegistrationResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
