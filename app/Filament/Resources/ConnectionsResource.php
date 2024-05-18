<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConnectionsResource\Pages;
use App\Filament\Resources\ConnectionsResource\RelationManagers;
use App\Models\Connections;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConnectionsResource extends Resource
{
    protected static ?string $model = Connections::class;

    protected static ?string $navigationIcon = 'heroicon-o-wifi';

    protected static ?string $navigationGroup = 'Main';

    public static function getModelLabel(): string
    {
        return trans_choice('admin.resources.connections', 2);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('admin.resources.connections', 2);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('uid')
                    ->required()
                    ->maxLength(64),
                Forms\Components\TextInput::make('room_id')
                    ->required()
                    ->maxLength(36),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(150),
                Forms\Components\Toggle::make('active')
                    ->required(),
                Forms\Components\TextInput::make('vote')
                    ->required(),
                Forms\Components\TextInput::make('data')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('uid')
                    ->searchable(),
                Tables\Columns\TextColumn::make('room_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConnections::route('/'),
            'create' => Pages\CreateConnections::route('/create'),
            'view' => Pages\ViewConnections::route('/{record}'),
            'edit' => Pages\EditConnections::route('/{record}/edit'),
        ];
    }
}
