<?php


namespace App\Filament\Resources\Settings\HomeBannerResource\Pages;

use App\Filament\Resources\Settings\HomeBannerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHomeBanners extends ListRecords
{
    protected static string $resource = HomeBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
