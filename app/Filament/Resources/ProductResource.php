<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                Repeater::make('images')
                    ->label('Product Media')
                    ->relationship('images') // Connects to the polymorphic relationship
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
                            ->acceptedFileTypes(['image/*', 'video/mp4', 'video/avi', 'video/mov', 'video/webm']) // Allow images & videos
                            ->maxSize(102400), // 100MB max
                    ])
                    ->minItems(1)
                    ->maxItems(10)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('supplier_user_id')
                    ->label('Supplier')
                    ->relationship('supplierUser', 'name', function (Builder $query) {
                        // Filter users with the 'Supplier' role
                        $query->whereHas('roles', function ($query) {
                            $query->where('name', 'Supplier'); // Assuming your roles are stored by name
                        });
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('category_id')
                    ->label('Category')
                    ->preload()
                    ->searchable()
                    ->relationship('category', 'name')
                    ->required()
                    ->live() // Triggers Livewire updates
                    ->afterStateUpdated(fn($state, callable $set) => $set('sub_category_id', null)), // Reset subcategory when category changes

                Select::make('sub_category_id')
                    ->label('Sub Category')
                    ->preload()
                    ->searchable()
                    ->relationship('subCategory', 'name')
                    ->required()
                    ->options(fn($get) => \App\Models\SubCategory::where('category_id', $get('category_id'))->pluck('name', 'id'))
                    ->live() // Ensures real-time updates
                    ->reactive(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('unit_selling_price')
                    ->required()
                    ->numeric()
                    ->prefix('PKR'),
                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\Toggle::make('sponsor')
                    ->required(),
                Forms\Components\Toggle::make('manzil_choice')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images.url')
                    ->label('Images')
                    ->circular()
                    ->size(50)
                    ->limit(3), // Show up to 3 images in a row
                Tables\Columns\TextColumn::make('supplierUser.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_selling_price')
                    ->label('Selling')
                    ->money('pkr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('manzil_choice')
                    ->label('Choice')
                    ->boolean(),
                Tables\Columns\IconColumn::make('sponsor')
                    ->label('Sponsor')
                    ->boolean(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
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
            'index' => Pages\ManageProducts::route('/'),
        ];
    }
}
