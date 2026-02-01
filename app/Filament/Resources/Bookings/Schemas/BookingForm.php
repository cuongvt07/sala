<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->required(),
                Select::make('room_id')
                    ->relationship('room', 'code')
                    ->searchable()
                    ->required(),
                DateTimePicker::make('check_in')->required(),
                DateTimePicker::make('check_out')->required(),
                TextInput::make('price')->numeric()->required()->prefix('$'),
                TextInput::make('deposit')->numeric()->prefix('$'),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'checked_in' => 'Checked In',
                        'checked_out' => 'Checked Out',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('pending')
                    ->required(),
                Textarea::make('notes')->columnSpanFull(),
            ]);
    }
}
