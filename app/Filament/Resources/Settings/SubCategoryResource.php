<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\SubCategoryResource\Pages;
use App\Filament\Resources\Settings\SubCategoryResource\RelationManagers;
use App\Models\SubCategory;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubCategoryResource extends Resource
{
    protected static ?string $model = SubCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Category'; 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([ 
                Select::make('category_id')
                ->relationship('category', 'name')
                    ->required(),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->nullable(),
                    FileUpload::make('image')
                        ->image()
                        ->nullable(), 
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
              
                ImageColumn::make('image')->circular()->default('https://ui-avatars.com/api/?name=SC&color=FFFFFF&background=020617'),
                TextColumn::make('category.name')->sortable()->searchable(),
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('description')->limit(50),
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
            'index' => Pages\ManageSubCategories::route('/'),
        ];
    }
}
