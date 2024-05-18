<?php

namespace App\Filament\Resources\ConnectionsResource\Pages;

use App\Filament\Resources\ConnectionsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConnections extends ListRecords
{
    protected static string $resource = ConnectionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
