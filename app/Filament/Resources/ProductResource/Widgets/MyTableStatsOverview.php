<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Filament\CustomStat\MyStat;
use App\Models\Product;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Filament\ProductFilament;
use Filament\Support\Colors\Color;

class MyTableStatsOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;
    protected static bool $isLazy = false;

    protected function getTablePage(): string
    {
        // throw new Exception('You must define a `getTablePage()` method on your widget that returns the name of a Livewire component.');
        return ListProducts::class;
    }

    protected function getStats(): array
    {
        return [
            // Stat::make('Tổng số sản phẩm', Product::count()),
            // MyStat::make('Tổng số sản phẩm', ProductFilament::count())
            Stat::make('Tổng số sản phẩm', $this->getTablePageInstance()->getTable()->getModel()::count())
                ->icon('heroicon-s-cube')
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17])
            ,Stat::make('Đang hiển thị', $this->getPageTableQuery()->count())
                ->icon('heroicon-s-cube-transparent')
                ->color(Color::Amber)
                ->chart([12, 2, 10, 3, 15, 29, 12])
            
        ];
    }
}
