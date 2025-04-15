<?php


namespace App\Filament\Resources\Settings\HomeBannerResource\Pages;

use App\Filament\Resources\Settings\HomeBannerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHomeBanner extends EditRecord
{
    protected static string $resource = HomeBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
