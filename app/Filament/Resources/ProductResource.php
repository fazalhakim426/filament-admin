<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Select::make('supplier_user_id')
                    ->label('Supplier')
                    ->relationship('supplierUser', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('category_id')
                    ->label('Category')
                    ->preload()
                    ->searchable()
                    ->relationship('category', 'name')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('sub_category_id', null)),

                Select::make('sub_category_id')
                    ->label('Sub Category')
                    ->preload()
                    ->searchable()
                    ->relationship('subCategory', 'name')
                    ->required()
                    ->options(fn ($get) => \App\Models\SubCategory::where('category_id', $get('category_id'))->pluck('name', 'id'))
                    ->live()
                    ->reactive(),

                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_active')->required(),
                Forms\Components\Toggle::make('sponsor')->required(),
                Forms\Components\Toggle::make('manzil_choice')->required(),

                // Adding Product Variants Repeater
                Repeater::make('productVariants')
                    ->label('Product Variants')
                    ->relationship('productVariants')
                    ->schema([
                        
                Repeater::make('images')
                ->label('Product Media')
                ->relationship('images')
                ->schema([
                    Select::make('type')
                        ->options([
                            'image' => 'Image',
                            'video' => 'Video',
                        ])
                        ->default('image')
                        ->required(),

                    FileUpload::make('url')
                        ->label('Upload File')
                        ->preserveFilenames()
                        ->directory('products/media')
                        ->required()
                        ->image()
                        ->acceptedFileTypes(['image/*', 'video/mp4', 'video/avi', 'video/mov', 'video/webm'])
                        ->maxSize(102400),
                ])
                ->minItems(1)
                ->maxItems(10)
                ->columnSpanFull(),
                        TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('unit_selling_price')
                            ->label('Unit Selling Price')
                            ->numeric()
                            ->required(),

                        // Nested repeater for variant options
                        Repeater::make('variantOptions')
                            ->label('Variant Options')
                            ->relationship('variantOptions') // Define in ProductVariant Model
                            ->schema([
                                TextInput::make('attribute_name')
                                    ->label('Attribute Name')
                                    ->placeholder('Color, Size, Material')
                                    ->required(),

                                TextInput::make('attribute_value')
                                    ->label('Attribute Value')
                                    ->placeholder('Red, Small, Cotton')
                                    ->required(),
                            ])
                            ->minItems(1)
                            ->maxItems(5)
                            
                            ->columnSpanFull(),
                    ])
                    ->minItems(1)
                    ->maxItems(10)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('supplierUser.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ManageProducts::route('/'),
        ];
    }
}
