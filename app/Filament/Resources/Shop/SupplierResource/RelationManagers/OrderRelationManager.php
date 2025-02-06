<?php

namespace App\Filament\Resources\Shop\SupplierResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Country;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'ordersAsSupplier';
    protected static ?string $label = 'Customers';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('warehouse_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customerUser.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('order_status')
                    ->badge()
                    ->color(fn($record) => $record->status === 'pending' ? 'gray' : ($record->status === 'confirmed' ? 'primary' : ($record->status === 'shipped' ? 'success' : ($record->status === 'delivered' ? 'success' : ($record->status === 'canceled' ? 'danger' : 'gray'))))),

                Tables\Columns\TextColumn::make('total_price')
                    ->searchable()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money(),
                    ]),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Price')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money(),
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->date()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\AttachAction::make(),
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DetachAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                // Tables\Actions\DetachBulkAction::make(),
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
