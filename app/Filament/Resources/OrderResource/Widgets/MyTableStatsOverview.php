<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyTableStatsOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;
    protected static bool $isLazy = false;

    protected function getTablePage(): string
    {
        // throw new Exception('You must define a `getTablePage()` method on your widget that returns the name of a Livewire component.');
        return ListOrders::class;
    }

    protected function getStats(): array
    {
        return [
            // Stat::make('Tổng số sản phẩm', Product::count()),
            // MyStat::make('Tổng số sản phẩm', ProductFilament::count())
            
            Stat::make('Tổng số đơn hàng', $this->getTablePageInstance()->getTable()->getModel()::count())
                ->icon('heroicon-s-document-duplicate')
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17])
            ,Stat::make('Đang hiển thị', $this->getPageTableQuery()->count())
                ->icon('heroicon-s-document')
                ->color(Color::Purple)
                ->chart([12, 2, 10, 3, 15, 29, 12])
            
        ];
    }
}
