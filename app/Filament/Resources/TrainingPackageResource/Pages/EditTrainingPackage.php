<?php

namespace App\Filament\Resources\TrainingPackageResource\Pages;

use App\Filament\Resources\TrainingPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrainingPackage extends EditRecord
{
    protected static string $resource = TrainingPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
