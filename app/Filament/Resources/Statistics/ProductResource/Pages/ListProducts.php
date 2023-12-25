<?php

namespace App\Filament\Resources\Statistics\ProductResource\Pages;

use App\Filament\Resources\Statistics\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;
    protected static ?string $breadcrumb = 'Sản phẩm';


    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
