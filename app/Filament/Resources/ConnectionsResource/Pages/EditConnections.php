<?php

namespace App\Filament\Resources\ConnectionsResource\Pages;

use App\Filament\Resources\ConnectionsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConnections extends EditRecord
{
    protected static string $resource = ConnectionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
