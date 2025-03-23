<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryMovementResource\Pages;
use App\Filament\Resources\InventoryMovementResource\RelationManagers;
use App\Models\InventoryMovement;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryMovementResource extends Resource
{
    protected static ?string $model = InventoryMovement::class;
    protected static ?string $navigationGroup = 'Shop';
    protected static ?string $slug = 'shop/inventroy';

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\View::make('forms.inventory-guide')
                    ->columnSpan('full'),
                Select::make('supplier_user_id')
                    ->label('Supplier')
                    ->relationship('supplierUser', 'name', function (Builder $query) {
                        $query->whereHas('roles', fn($query) => $query->where('name', 'Supplier'));
                    })
                    ->required()
                    ->preload()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn(callable $set) => $set('product_id', null)), // Reset product when supplier changes
    
                // Product Selection
                Select::make('product_id')
                    ->label('Product')
                    ->required()
                    ->options(fn(callable $get) => 
                        $get('supplier_user_id') 
                            ? \App\Models\Product::where('supplier_user_id', $get('supplier_user_id'))->pluck('name', 'id') 
                            : []
                    )
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn(callable $set) => $set('product_variant_id', null)), // Reset variant when product changes
     
                Select::make('product_variant_id')
                    ->label('Product Variant')
                    ->required()
                    ->options(fn(callable $get) => 
                        $get('product_id') 
                            ? \App\Models\ProductVariant::where('product_id', $get('product_id'))->pluck('sku', 'id') 
                            : []
                    )
                    ->searchable()
                    ->preload(),
    
                // Transaction Type
                Select::make('type')
                    ->label('Transaction Type')
                    ->required()
                    ->options([
                        'addition' => 'Addition',
                        'deduction' => 'Deduction',
                    ])
                    ->default('addition'),
    
                // Quantity
                TextInput::make('quantity')
                    ->label('Quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1),
    
                // Description
                TextInput::make('description')
                    ->label('Description')
                    ->required(),
    
                // Unit Cost Price
                TextInput::make('unit_price')
                    ->label('Unit Cost Price')
                    ->required()
                    ->minValue(1)
                    ->numeric(),
            ]);
    }
    


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('supplierUser.name')
                    ->label('Supplier')
                    ->searchable(),
                    TextColumn::make('orderItem.order.warehouse_number')
                        ->label('Order')
                        ->searchable(),
                        TextColumn::make('product.name')
                            ->label('Product')
                            ->searchable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable()
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->label('Unit Price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                ->badge()
                ->color(fn($record) => match ($record->type) {
                    'addition' => 'gray',
                    'deduction' => 'danger', 
                }), 
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ManageInventoryMovements::route('/'),
        ];
    }
}
