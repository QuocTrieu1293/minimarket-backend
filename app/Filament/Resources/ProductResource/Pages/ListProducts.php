<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListProducts extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = ProductResource::class;
   
    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()
            
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProductResource\Widgets\ProductTableStatsOverview::class
        ];
    }

}
