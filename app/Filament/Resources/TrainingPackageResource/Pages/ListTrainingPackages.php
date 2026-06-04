<?php

namespace App\Filament\Resources\TrainingPackageResource\Pages;

use App\Filament\Resources\TrainingPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrainingPackages extends ListRecords
{
    protected static string $resource = TrainingPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
