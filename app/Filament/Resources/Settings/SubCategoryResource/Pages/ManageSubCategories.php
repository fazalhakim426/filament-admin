<?php

namespace App\Filament\Resources\Settings\SubCategoryResource\Pages;

use App\Filament\Resources\Settings\SubCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSubCategories extends ManageRecords
{
    protected static string $resource = SubCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
