<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class CustomerBirthdaysWidget extends BaseWidget
{
    protected static ?string $heading = 'Sinh nhật khách hàng hôm nay';

    protected static ?int $sort = 2; // Hiển thị dưới Account Widget

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Only find customers where the day and month of dob matches today
                Customer::query()
                    ->whereNotNull('dob')
                    ->whereMonth('dob', now()->month)
                    ->whereDay('dob', now()->day)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Họ tên')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dob')
                    ->label('Sinh nhật')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Số điện thoại'),
            ])
            ->emptyStateHeading('Không có khách hàng nào')
            ->emptyStateDescription('Hôm nay không có khách hàng nào sinh nhật.');
    }
}
