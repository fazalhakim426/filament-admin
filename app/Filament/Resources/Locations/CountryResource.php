<?php

namespace App\Filament\Resources\Locations;

use App\Filament\Resources\Locations\CountryResource\Pages;
use App\Filament\Resources\Locations\CountryResource\RelationManagers;
use App\Models\Country;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationGroup = 'Locations'; 
    protected static ?int $navigationSort = 1;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('code')->required()->unique(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
               
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('code'),
            ])
            ->defaultSort('id','desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCountries::route('/'),
        ];
    }
}
