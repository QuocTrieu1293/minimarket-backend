<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = "Tạo sản phẩm mới";

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if(empty($data['discount_percent'])) 
            $data['discount_percent'] = 0;
        
        $data['discount_price'] = preg_replace('/\D/','',$data['discount_price']);
        
        if(empty($data['quantity'])) {
            $data['quantity'] = 0;
            $data['is_visible'] = false;
        }
        if(!empty($data['canonical']))
            $data['canonical'] .= $data['_unit'];

        // dd($data);
        return $data;
    }
}
