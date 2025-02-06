<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Shop\OrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Squire\Models\Currency;

class LatestOrders extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(OrderResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('warehouse_number')
                    ->label('Warehouse'),
                Tables\Columns\TextColumn::make('customerUser.name')
                    ->label('Customer')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_status')
                    ->badge()
                    ->color(fn($record) => $record->status === 'pending' ? 'gray' : ($record->status === 'confirmed' ? 'primary' : ($record->status === 'shipped' ? 'success' : ($record->status === 'delivered' ? 'success' : ($record->status === 'canceled'||$record->status === 'refunded' ? 'danger' : 'gray'))))),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Cost')
                    ->prefix('RS')
                    ->sortable(),
            ])
            ->actions([
                // Tables\Actions\Action::make('open')
                //     ->url(fn (Order $record): string => OrderResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
