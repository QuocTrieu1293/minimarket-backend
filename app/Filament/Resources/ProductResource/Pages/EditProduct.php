<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = "Chỉnh sửa sản phẩm";

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // dd($data);
        $data['reg_price'] = (int)$data['reg_price'];
        $data['discount_price'] = moneyFormat($data['discount_price']);
        if(!empty($data['canonical']) && 
            preg_match('/\d+(\.\d+)?\s*(-\s*\d+(\.\d+)?)?/', $data['canonical'], $matches)) {
            $unit = preg_split('/\d+(\.\d+)?\s*(-\s*\d+(\.\d+)?)?/',$data['canonical']);
            $unit = $unit[count($unit)-1];
            $unit = preg_replace('/\s+/','',$unit);
            // dd($data['canonical'],$matches[0],$unit);
            $data['_unit'] = match($unit) {
                'lít', 'l' => 'lít',
                'ml' => 'ml',
                'kg' => 'kg',
                'g', 'gr', 'gam', 'gram' => 'g',
                default => null
            };
            $data['canonical'] = preg_replace('/\s+/','',$matches[0]);
        }
        // dd($data['canonical']);
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // dd($data);
        if(!isset($data['discount_percent'])) 
            $data['discount_percent'] = 0;
        $data['discount_price'] = preg_replace('/\D/','',$data['discount_price']);
        if(empty($data['quantity'])) {
            $data['quantity'] = 0;
            $data['is_visible'] = false;
        }
        if(!empty($data['canonical']))
            $data['canonical'] .= $data['_unit'];
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
