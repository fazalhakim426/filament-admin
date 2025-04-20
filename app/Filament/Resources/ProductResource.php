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
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;

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
                    ->afterStateUpdated(fn($state, callable $set) => $set('sub_category_id', null)),

                Select::make('sub_category_id')
                    ->label('Sub Category')
                    ->preload()
                    ->searchable()
                    ->relationship('subCategory', 'name')
                    ->required()
                    ->options(fn($get) => \App\Models\SubCategory::where('category_id', $get('category_id'))->pluck('name', 'id'))
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

                // Repeater::make('specifications')
                //     ->label('Specifications')
                //     ->relationship('specifications')
                //     ->schema([
                //         TextInput::make('key')
                //             ->label('Specification Key')
                //             ->placeholder('e.g. Color, Material')
                //             ->required(),

                //         TextInput::make('value')
                //             ->label('Specification Value')
                //             ->placeholder('e.g. Black, Cotton')
                //             ->required(),
                //     ])
                //     ->minItems(1)
                //     ->maxItems(10)
                //     ->columnSpanFull(),

            ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('supplierUser.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                    ImageColumn::make('variant_images')
                    ->label('Images')
                    ->getStateUsing(function ($record) {
                        // Safely get the first variant with images
                        $variant = $record->productVariants->firstWhere(fn($v) => $v->images->isNotEmpty());
                        
                        // Return empty array if no variant with images found
                        if (!$variant) {
                            return [];
                        }
                        
                        // Get first 3 image URLs, filtering out any null values
                        return $variant->images
                            ->take(3)
                            ->filter()
                            ->pluck('url')
                            ->filter()
                            ->toArray();
                    })
                    ->circular()
                    ->stacked()
                    ->default('No images available'),


                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('id')
                    ->label('Variants & Options')
                    ->formatStateUsing(function ($record) {
                        return $record->productVariants
                            ->take(3)
                            ->map(function ($variant) {
                                $options = $variant->variantOptions
                                    ->take(3)
                                    ->map(fn($opt) => "{$opt->attribute_name}: {$opt->attribute_value}")
                                    ->join(', ');

                                return "SKU - {$variant->sku} ({$options}) - RS {$variant->unit_selling_price}";
                            })
                            ->join('<br><br>');
                    })
                    ->html()
                    ->wrap()
                    ->limit(200),




                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(function (Product $record) {
                        try {
                            $record->delete();
                        } catch (\Illuminate\Database\QueryException $e) {
                            if ($e->getCode() === '23000') {
                                Notification::make()
                                    ->title('Unable to delete supplier')
                                    ->body('This product is linked to inventory records and cannot be deleted.')
                                    ->danger()
                                    ->persistent()
                                    ->send();
                                return;
                            }
                            throw $e;
                        }
                    }),
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
