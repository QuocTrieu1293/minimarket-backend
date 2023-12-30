<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderItemResource;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Exception;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function add(Request $request) {
        // userId, address, payment_method, note
        // try {
            $request->validate([
                'userId' => 'required',
                'address' => 'required',
                'payment_method' => 'required',
                'note' => 'nullable'
            ]);
            $cart = User::findOrFail($request->userId)->cart;
            $items = $cart->cart_items;
            if($items->count() === 0) {
                throw new Exception("Không thể tạo đơn hàng do giỏ hàng hiện đang rỗng");
            }
            // dd($cartItems);

            $order = Order::create([
                'address' => $request->address,
                'total' => $cart->total, 
                'note' => $request->note, 
                'payment_method' => $request->payment_method, 
                'user_id' => $request->userId
            ]);
            foreach($items as $item) {
                $product = $item->product;
                $sale_item = $product->sale_item;
                if(($sale_item && $sale_item->remain >= $item->quantity) || (!$sale_item)) {
                    OrderItem::create([
                        'unit_price' => $product->getSalePrice(), //event_price 
                        'quantity' => $item->quantity, 
                        'order_id' => $order->id, 
                        'product_id' => $item->product_id, 
                        'total_price' => $item->total,
                        'from_event' => 
                            ($sale_item && $sale_item->remain >= $item->quantity) ? true : false
                    ]);
                    
                }else {
                    //Phần khuyễn mãi
                    OrderItem::create([
                        'unit_price' => $product->getSalePrice(), //event_price 
                        'quantity' => $sale_item->remain, 
                        'order_id' => $order->id, 
                        'product_id' => $item->product_id, 
                        'total_price' => $product->getSalePrice() * $sale_item->remain,
                                                //event_price
                        'from_event' => true
                    ]); 
                    //Phần không được khuyễn mãi
                    OrderItem::create([
                        'unit_price' => $product->discount_price, 
                        'quantity' => $item->quantity - $sale_item->remain, 
                        'order_id' => $order->id, 
                        'product_id' => $item->product_id, 
                        'total_price' => $product->discount_price * ($item->quantity - $sale_item->remain),
                        'from_event' => false
                    ]);
                }

                if($sale_item && $sale_item->remain) {
                    $sale_item->remain -= $item->quantity;
                    $sale_item->remain = max($sale_item->remain, 0);
                    $sale_item->save();
                }
                if($sale_item && $sale_item->remain === 0) {
                    $product->event_percent = null;
                    $product->event_price = null;

                    $product->timestamps = false;
                    $product->save(); //ngăn không cập nhật trường updated_at
                    $product->timestamps = true;
                }
            }
            $cart->cart_items()->delete();
            return [
                'id' => $order->id,
            ];
        // }catch(Exception $e) {
        //     $statusCode = ($e instanceof ValidationException) ? 422 : 404;
        //     return response()->json(['error' => $e->getMessage()], $statusCode);
        // }
    }

    public function get($id) {
        try {
            $order = Order::findOrFail($id);
            return [
                'id' => $order->id,
                'address' => $order->address,
                'total' => $order->total,
                'note' => $order->note,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'date' => $order->created_at,
                'list' => OrderItemResource::collection($order->order_items)
            ];
        }catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
