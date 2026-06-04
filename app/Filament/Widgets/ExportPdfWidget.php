<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class ExportPdfWidget extends Widget
{
    protected static string $view = 'filament.widgets.export-pdf-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 0;
}