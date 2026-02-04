<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
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
                \Filament\Forms\Components\Grid::make(3)
                    ->schema([
                        TextInput::make('deposit')->label('Cọc Lần 1')->numeric()->prefix('$'),
                        TextInput::make('deposit_2')->label('Cọc Lần 2')->numeric()->prefix('$'),
                        TextInput::make('deposit_3')->label('Cọc Lần 3')->numeric()->prefix('$'),
                    ]),
                Select::make('status')
                    ->options([
                        'pending' => 'Chờ lấy phòng',
                        'checked_in' => 'Đã nhận phòng',
                        'checked_out' => 'Đã trả phòng',
                        'cancelled' => 'Đã hủy',
                    ])
                    ->default('pending')
                    ->required(),
                Textarea::make('notes')->columnSpanFull(),
            ]);
    }
}
