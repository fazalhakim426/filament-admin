<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
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

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 10;
    protected static ?string $recordTitleAttribute = 'email';

    public static function globalSearchColumns(): array
    {
        return [
            'id',
            'email',
            'name',
            'referral_code'
        ];
    }
    public static function form(Form $form): Form
    {
        return $form
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
            ]);
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
                TextColumn::make('roles')
                    ->badge()
                    ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : $state) // Combine roles as a comma-separated string
                    ->color('primary') // Optional: Set a default color for badges
                    ->extraAttributes(['class' => 'space-x-1']) // Adds spacing between badges

                    ->getStateUsing(fn(User $record) => $record->roles->pluck('name')->join(', ')), // Fetch roles and join them as a string

            ])
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
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
