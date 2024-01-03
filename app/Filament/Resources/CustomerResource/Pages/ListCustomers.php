<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\CustomerResource\Widgets;

class ListCustomers extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = CustomerResource::class;

    protected static ?string $title = "Khách hàng";

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            Widgets\MyTableStatsOverview::class
        ];
    }
}
