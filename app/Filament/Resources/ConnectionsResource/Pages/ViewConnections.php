<?php

namespace App\Filament\Resources\ConnectionsResource\Pages;

use App\Filament\Resources\ConnectionsResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewConnections extends ViewRecord
{
    protected static string $resource = ConnectionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
