<?php

namespace App\Filament\Resources\Locations\CityResource\Pages;

use App\Filament\Resources\Locations\CityResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCities extends ManageRecords
{
    protected static string $resource = CityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
