<?php

namespace App\Filament\Resources\Rooms\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class RoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('area_id')
                    ->relationship('area', 'name')
                    ->required(),
                TextInput::make('code')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->options([
                        'Studio' => 'Studio',
                        '1BR' => '1 Bedroom',
                        '2BR' => '2 Bedroom',
                        'Penthouse' => 'Penthouse',
                    ])
                    ->required(),
                TextInput::make('price')
                    ->numeric()
                    ->default(0),
                Select::make('status')
                    ->options([
                        'available' => 'Available',
                        'occupied' => 'Occupied',
                        'maintenance' => 'Maintenance',
                    ])
                    ->default('available')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
