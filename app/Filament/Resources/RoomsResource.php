<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Rooms;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\RoomsResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\RoomsResource\RelationManagers;
use App\Filament\Resources\RoomsResource\RelationManagers\TasksRelationManager;

class RoomsResource extends Resource
{
    protected static ?string $model = Rooms::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Main';

    public static function getModelLabel(): string
    {
        return trans_choice('admin.resources.rooms', 2);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('admin.resources.rooms', 2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('hash')
                    ->required()
                    ->maxLength(36),
                Forms\Components\TextInput::make('stage')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('owner')
                    ->required()
                    ->maxLength(64),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->maxLength(50),
                Forms\Components\TextInput::make('active_task_id')
                    ->numeric(),
                \ValentinMorice\FilamentJsonColumn\FilamentJsonColumn::make('cardset'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hash')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stage')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('owner')
                    ->searchable(),
                Tables\Columns\TextColumn::make('active_task_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TasksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRooms::route('/create'),
            'view' => Pages\ViewRooms::route('/{record}'),
            'edit' => Pages\EditRooms::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
        ->schema([
            Infolists\Components\TextEntry::make('name'),
            Infolists\Components\TextEntry::make('hash'),
            Infolists\Components\TextEntry::make('cardset'),
        ])
        ->columns(1);
    }
}
