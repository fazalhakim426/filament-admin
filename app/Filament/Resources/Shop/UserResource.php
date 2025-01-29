<?php

namespace App\Filament\Resources\Shop;

use App\Filament\Resources\Shop\CustomerResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\Shop\CustomerResource\RelationManagers\AddressesRelationManager;
use App\Filament\Resources\Shop\CustomerResource\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\Shop\CustomerResource\RelationManagers\ReferralRelationManager;
use App\Filament\Resources\Shop\UserResource\Pages;
use App\Models\Referral;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $label = 'Customers';

    protected static ?string $slug = 'shop/users';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = 'Shop';
    protected static ?string $title = 'Customer';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 2;
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('addresses')->whereHas('roles', function ($query) {
            $query->where('name', 'Customer'); // Filter by "Customer" role
        })->withoutGlobalScope(SoftDeletingScope::class);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([  
            
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(50),
                            TextInput::make('email')->email()->required()
                                ->label('Email'),
            
                            FileUpload::make('profile_photo_path')
                                ->directory('images') // Stored in storage/app/public/images
                                ->disk('public')      // Use the public disk
                            ->visibility('public')
                            ,
                            Forms\Components\Textarea::make('address')
                                ->columnSpanFull(),
            
                            Forms\Components\TextInput::make('contact_number')
                                ->maxLength(15),
            
                            Forms\Components\TextInput::make('whatsapp_number')
                                ->maxLength(15),
            
                            Select::make('city_id')
                                ->label('City')
                                ->relationship(name: 'city', titleAttribute: 'name'),
                            Forms\Components\Toggle::make('active')
                                ->required(),
                            Forms\Components\Select::make('roles')
                                ->relationship(name: 'roles', titleAttribute: 'name')
                                ->saveRelationshipsUsing(function (Model $record, $state) {
                                    $record->roles()->sync($state);
                                })
                                ->multiple()
                                ->preload()
                                ->searchable(),
                        

                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => fn(?User $record) => $record === null ? 3 : 2]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn(User $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn(User $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn(?User $record) => $record === null),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_photo_path')->label('Photo')->default('https://ui-avatars.com/api/?name=X&color=FFFFFF&background=020617'),
                TextColumn::make('name')->label('Name')->sortable()->searchable(),
                TextColumn::make('email')->label('Email')->sortable()->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('city.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('whatsapp_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('referral_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('balance')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('roles')
                //     ->badge()
                //     ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : $state) // Combine roles as a comma-separated string
                //     ->color('primary') // Optional: Set a default color for badges
                //     ->extraAttributes(['class' => 'space-x-1']) // Adds spacing between badges

                //     ->getStateUsing(fn(User $record) => $record->roles->pluck('name')->join(', ')), // Fetch roles and join them as a string

            ])
            ->filters([
                // Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->action(function () {
                        Notification::make()
                            ->title('Now, now, don\'t be cheeky, leave some records for others to play with!')
                            ->warning()
                            ->send();
                    }),
            ]);
    }

    /** @return Builder<User> */


    public static function getRelations(): array
    {
        return [
            AddressesRelationManager::class,
            PaymentsRelationManager::class,
            OrdersRelationManager::class,
            ReferralRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }
}
