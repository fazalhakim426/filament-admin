<?php
namespace App\Filament\Resources\UserResource\Pages;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource;
use Filament\Resources\Components\Tab;
use Filament\Actions;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
  
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('active', true)),
            'inactive' => Tab::make('Inactive')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('active', false)),
        ];
    }
}
