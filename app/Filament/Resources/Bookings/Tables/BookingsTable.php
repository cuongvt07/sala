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
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('customer.name')->searchable(),
                TextColumn::make('room.code')->searchable(),
                TextColumn::make('check_in')->dateTime()->sortable(),
                TextColumn::make('check_out')->dateTime()->sortable(),
                TextColumn::make('deposits')
                    ->label('Tình trạng cọc & thanh toán')
                    ->html()
                    ->state(function (\App\Models\Booking $record): string {
                        $html = '';
                        $totalCọc = $record->deposit + $record->deposit_2 + $record->deposit_3;
                        $totalPrice = $record->price ?? 0;
                        
                        // Hiển thị dải tiền cọc
                        if ($record->deposit > 0)
                            $html .= '<div class="text-xs text-gray-700">L1: ' . number_format($record->deposit, 0, ',', '.') . '</div>';
                        if ($record->deposit_2 > 0)
                            $html .= '<div class="text-xs text-gray-700">L2: ' . number_format($record->deposit_2, 0, ',', '.') . '</div>';
                        if ($record->deposit_3 > 0)
                            $html .= '<div class="text-xs text-gray-700">L3: ' . number_format($record->deposit_3, 0, ',', '.') . '</div>';
                            
                        // Hiển thị Tình trạng thanh toán
                        if ($totalCọc > 0) {
                            if ($totalCọc >= $totalPrice) {
                                $html .= '<div class="mt-1 text-xs font-semibold text-green-600 rounded bg-green-50 px-1 py-0.5 inline-block">Đã thanh toán</div>';
                            } else {
                                $html .= '<div class="mt-1 text-xs font-semibold text-orange-600 rounded bg-orange-50 px-1 py-0.5 inline-block">Sắp đến hạn / Còn thiếu</div>';
                            }
                        }

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
