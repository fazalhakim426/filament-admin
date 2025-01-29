<?php

namespace App\Filament\Resources\Shop; 
use App\Filament\Resources\Shop\SupplierResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\FileUpload; 
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

class SupplierResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $label = 'Supplier';

    protected static ?string $slug = 'shop/supplier';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = 'Shop';
    protected static ?string $title = 'Supplier';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 2;
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('addresses')->whereHas('roles', function ($query) {
            $query->where('name', 'Supplier');
        })->withoutGlobalScope(SoftDeletingScope::class);
    }
    protected function afterSave(): void
    {  
            $this->record->assignRole('Supplier'); 
        $this->record->supplierDetail()->create( 
            ['business_name' => $this->form->getState('business_name')]
        );
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['business_name'] = $this->record->supplierDetail->business_name ?? null;
        return $data;
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
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->label('Email'),
                        Forms\Components\Textarea::make('address')
                            ->columnSpanFull(),
                        
                        // add here the supplier detial
                        
                        Forms\Components\TextInput::make('contact_number')
                            ->maxLength(15),
                        Forms\Components\TextInput::make('whatsapp_number')
                            ->maxLength(15),
                        
                        Forms\Components\Select::make('city_id')
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
                        
                        FileUpload::make('profile_photo_path')
                            ->directory('images')
                            ->disk('public')
                            ->visibility('public'),
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
                // Tables\Columns\TextColumn::make('referral_code')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('balance')
                //     ->numeric()
                //     ->sortable(),
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
            // RecipientRelationManager::class,
            // PaymentsRelationManager::class,
            // OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }
}
