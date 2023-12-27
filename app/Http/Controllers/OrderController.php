<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Exception;

class OrderController extends Controller
{
    public function add(Request $request) {
        // userId, address, payment_method, note
        $request->validate([
            'userId' => 'required',
            'address' => 'required',
            'payment_method' => 'required',
            'note' => 'nullable'
        ]);
        
        try {
            $cart = User::findOrFail($request->userId)->cart;
            $items = $cart->cart_items;
            // dd($cartItems);

            $order = Order::create([
                'address' => $request->address,
                'total' => $cart->total, 
                'note' => $request->note, 
                'payment_method' => $request->payment_method, 
                'user_id' => $request->userId
            ]);
            foreach($items as $item) {
                OrderItem::create([
                    'unit_price' => $item->product->discount_price, 
                    'quantity' => $item->quantity, 
                    'order_id' => $order->id, 
                    'product_id' => $item->product_id, 
                    'total_price' => $item->total
                ]);
            }
            $cart->cart_items()->delete();
            return [
                'oderId' => $order->id,
                'totalAmount'=> $order->total
            ];
        }catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
