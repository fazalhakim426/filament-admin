<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\HomeBannerResource\Pages;
use App\Filament\Resources\HomeBannerResource\RelationManagers;
use App\Filament\Resources\Settings\HomeBannerResource\Pages\CreateHomeBanner;
use App\Filament\Resources\Settings\HomeBannerResource\Pages\EditHomeBanner;
use App\Filament\Resources\Settings\HomeBannerResource\Pages\ListHomeBanners;
use App\Models\HomeBanner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table; 
use App\Models\Product;
use App\Models\User;
use Filament\Forms\Get;
use Filament\Forms\Set;

class HomeBannerResource extends Resource
{
    protected static ?string $model = HomeBanner::class;
 

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = 'Category';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Temporary supplier select (not saved)
                Forms\Components\Select::make('temp_supplier_id')
                ->label('Supplier')
                ->options(User::whereHas('products')->pluck('name', 'id'))
                ->reactive()
                ->dehydrated(false)  
                ->afterStateUpdated(fn(Set $set) => $set('display_order', null)),
            
                // Actual product select (will be saved)
                Forms\Components\Select::make('display_order')
                    ->label('Product')
                    ->options(function (Get $get) {
                        $supplierId = $get('temp_supplier_id');

                        if (!$supplierId) return Product::pluck('name', 'id');

                        return Product::where('supplier_user_id', $supplierId)
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('title')->maxLength(255),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
                Forms\Components\FileUpload::make('image_url')->image()->required(),
                Forms\Components\TextInput::make('button_text')->maxLength(100),
                Forms\Components\TextInput::make('button_link')->maxLength(512), 
                Forms\Components\Toggle::make('is_active')->required(),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image_url'),
                Tables\Columns\TextColumn::make('button_text')
                    ->searchable(),
                Tables\Columns\TextColumn::make('button_link')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHomeBanners::route('/'),
            'create' => CreateHomeBanner::route('/create'),
            'edit' => EditHomeBanner::route('/{record}/edit'),
        ];
    }
}
