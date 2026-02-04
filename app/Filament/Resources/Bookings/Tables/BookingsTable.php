<?php

namespace App\Filament\Resources\Bookings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')->searchable(),
                TextColumn::make('room.code')->searchable(),
                TextColumn::make('check_in')->dateTime()->sortable(),
                TextColumn::make('check_out')->dateTime()->sortable(),
                TextColumn::make('deposits')
                    ->label('Đặt cọc')
                    ->html()
                    ->state(function (\App\Models\Booking $record): string {
                        $html = '';
                        if ($record->deposit > 0)
                            $html .= '<div class="text-xs">L1: ' . number_format($record->deposit, 0, ',', '.') . '</div>';
                        if ($record->deposit_2 > 0)
                            $html .= '<div class="text-xs">L2: ' . number_format($record->deposit_2, 0, ',', '.') . '</div>';
                        if ($record->deposit_3 > 0)
                            $html .= '<div class="text-xs">L3: ' . number_format($record->deposit_3, 0, ',', '.') . '</div>';
                        return $html ?: '<span class="text-gray-400">-</span>';
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'checked_in' => 'success',
                        'checked_out' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
