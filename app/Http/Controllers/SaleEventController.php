<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use App\Models\SaleEvent;
use Illuminate\Support\Facades\Redis;
use Exception;

class SaleEventController extends Controller
{
    public function getSaleEvent()
    {
        try {
            $event = Redis::get('sale_event');
            if (!$event) {
                $event = SaleEvent::findOrFail(1);
                Redis::setex('sale_event', 60, serialize($event));
            } else {
                $event = unserialize($event);
            }
            $response = [
                'id' => 1,
                'name' => $event->name,
                'description' => $event->description,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time
            ];
            return $response;
        } catch (Exception $e) {
            return response()
                ->json(['error' => 'id của sale event phải là 1. Kiểm tra lại db'], 404);
        }
    }

    public function getSaleItems()
    {
        try {
            $saleItems = Redis::get('sale_items');
            if (!$saleItems) {
                $event = Redis::get('sale_event');
                if (!$event) {
                    $event = SaleEvent::findOrFail(1);
                    Redis::setex('sale_event', 60, serialize($event));
                } else {
                    $event = unserialize($event);
                }
                $saleItems = $event->sale_items->map(fn ($item) => $item->product);
                Redis::setex($saleItems, 30, serialize($saleItems));
            } else {
                $saleItems = unserialize($saleItems);
            }
            return response()->json(ProductResource::collection($saleItems));
        } catch (Exception $e) {
            return response()
                ->json(['error' => 'id của sale event phải là 1. Kiểm tra lại db'], 404);
        }
    }
}
