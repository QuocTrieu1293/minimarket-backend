<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use App\Models\SaleEvent;
use Exception;

class SaleEventController extends Controller
{
    public function getSaleEvent() {
        try {
            $event = SaleEvent::findOrFail(1);
            return [
                'id' => 1,
                'name' => $event->name,
                'description' => $event->description,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time
            ];
        }catch(Exception $e) {
            return response()
                    ->json(['error' => 'id của sale event phải là 1. Kiểm tra lại db'], 404);
        }
    }

    public function getSaleItems() {
        try {
            $event = SaleEvent::findOrFail(1);
            $eventProducts = $event->sale_items->map(fn($item) => $item->product);
            return response()->json(ProductResource::collection($eventProducts));
        }catch(Exception $e) {
            return response()
                    ->json(['error' => 'id của sale event phải là 1. Kiểm tra lại db'], 404);
        }
    }
}
