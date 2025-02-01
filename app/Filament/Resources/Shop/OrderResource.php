<?php

namespace App\Filament\Resources\Shop;

use App\Filament\Resources\Shop\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextArea;
use Filament\Tables\Actions\Action;
use Filament\Resources\Resource;
use Illuminate\Support\Carbon;
use Filament\Tables\Table;
use Filament\Forms\Form;
use App\Models\Order;
use Filament\Tables;
use Filament\Forms;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Address;
use Filament\Forms\Components\{Wizard, Wizard\Step, Select, TextInput, Repeater, Grid, Section};
use Filament\Resources\Pages\CreateRecord;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $slug = 'shop/orders';

    protected static ?string $recordTitleAttribute = 'warehouse';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 1;


    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // Step 1: Order Item Details
                    Step::make('Order Items')
                        ->schema([
                            Select::make('customer_user_id')
                                ->label('Select Customer')
                                ->relationship('customer', 'name')
                                ->required(),

                            Repeater::make('order_items')
                                ->relationship()
                                ->schema([
                                    Select::make('product_id')
                                        ->label('Product')
                                        ->relationship('product', 'name')
                                        ->searchable()
                                        ->required(),

                                    TextInput::make('quantity')
                                        ->label('Quantity')
                                        ->numeric()
                                        ->default(1)
                                        ->required(),

                                    TextInput::make('price')
                                        ->label('Price')
                                        ->numeric()
                                        ->default(0.00)
                                        ->required(),
                                ])
                                ->columns(3)
                                ->createItemButtonLabel('Add Product'),
                        ]),

                    // Step 2: Sender Details
                    Step::make('Sender Details')
                        ->schema([
                            Select::make('sender_id')
                                ->label('Select Sender Address')
                                ->relationship('sender', 'address')
                                ->searchable()
                                ->required(),
                        ]),

                    // Step 3: Recipient Details
                    Step::make('Recipient Details')
                        ->schema([
                            Select::make('recipient_id')
                                ->label('Select Recipient Address')
                                ->relationship('recipient', 'address')
                                ->searchable()
                                ->required(),

                            TextInput::make('total_price')
                                ->label('Total Price')
                                ->numeric()
                                ->default(0.00)
                                ->required(),
                        ]),
                ])
                    ->skippable(),
            ]);
    }

    public static function getDetailsFormSchema(): array
    {
        return [
            TextInput::make('warehouse_number')->label('Warehouse Number')->required(),
            Select::make('customer_user_id')
                ->label('Customer')
                ->relationship('customer', 'name')
                ->required(),
            // Add other form components as needed...
        ];
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('warehouse_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customerUser.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('recipient.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sender.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
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
                // Tables\Filters\TrashedFilter::make(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->placeholder(fn($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('created_until')
                            ->placeholder(fn($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Order from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Order until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->actions([

                Action::make('view')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->modalHeading('Order Details')
                    ->modalContent(function ($record) {
                        return view('admin.orders.details', [
                            'order' => $record,
                            'items' => $record->items,
                            'sender' => $record->sender,
                            'recipient' => $record->recipient
                        ]);
                    })
                    ->action(fn() => Notification::make()->title('Details shown')->success()->send()),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->action(function () {
                        Notification::make()
                            ->title('Now, now, don\'t be cheeky, leave some records for others to play with!')
                            ->warning()
                            ->send();
                    }),
            ])
            ->groups([
                Tables\Grouping\Group::make('created_at')
                    ->label('Order Date')
                    ->date()
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            // OrderStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    /** @return Builder<Order> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['warehouse_number', 'customerUser.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Order $record */

        return [
            'customerUser' => optional($record->customerUser)->name,
        ];
    }

    /** @return Builder<Order> */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['customerUser']);
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::count();
    }
}
