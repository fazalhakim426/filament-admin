<?php

namespace App\Filament\Resources\ReferralResource\Pages;

use App\Filament\Resources\ReferralResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
class ListReferrals extends ListRecords
{
    protected static string $resource = ReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'released' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('reward_released', true)),
            'no released' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('reward_released', false)),
        ];
    }

}
