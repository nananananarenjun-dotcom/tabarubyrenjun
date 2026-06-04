<?php

namespace App\Filament\Resources\TrainingPackageResource\Pages;

use App\Filament\Resources\TrainingPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTrainingPackage extends CreateRecord
{
    protected static string $resource = TrainingPackageResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
