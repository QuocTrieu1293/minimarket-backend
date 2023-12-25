<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartItemResource;
use Illuminate\Http\Request;
use Exception;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{

    protected function response(Cart $cart) {
        return [
            'quantity' => $cart->quantity,
            'total' => $cart->total,
            'savings' => $cart->savings,
            'list' => CartItemResource::collection($cart->cart_items)
        ];
    }

    public function getItems($id) {
        try {
            return $this->response(Cart::findOrFail($id));
        }catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    //thêm vào ~ cộng dồn quantity
    public function addItem(Request $request) {
        // request: productId, cartId, quantity
        $validator = Validator::make(
            $request->all(),
            [
                'quantity' => 'required|integer|min:1',
                'productId' => 'required',
                'cartId' => 'required'
            ]
        );
        if($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }
        try {
            $product = Product::findOrFail($request->productId);
            $cartItem = Cart::findOrFail($request->cartId)->cart_items()
                        ->whereHas('product', fn($query) => $query->where('id',$request->productId))
                        ->first();
            $quantity = $request->quantity;
            if(!$cartItem) {
                $cartItem = new CartItem;
                $cartItem->cart_id = $request->cartId;
                $cartItem->product_id = $request->productId;
            }else { 
                $quantity += $cartItem->quantity;
            }
            $cartItem->quantity = $quantity;
            $cartItem->total = $quantity * $product->discount_price;
            $cartItem->savings = $quantity * ($product->reg_price - $product->discount_price);
            $cartItem->save(); 
            return $this->response(Cart::find($request->cartId));
        }catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    //Cập nhật quantity của một cart_item
    //quantity = 0 ~ xoá cart_item đó
    public function updateItem(Request $request, $itemId) {
        // request: quantity
        $validator = Validator::make(
            $request->all(),
            [
                'quantity' => 'required|integer|min:0',
            ]
        );
        if($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }
        try {
            $cartItem = CartItem::findOrFail($itemId);
            if($request->quantity > 0 && $request->quantity != $cartItem->quantity) {
                $product = $cartItem->product;
                $cartItem->update([
                    'quantity' => $request->quantity,
                    'total' => $request->quantity * $product->discount_price,
                    'savings' => $request->quantity * ($product->reg_price - $product->discount_price)
                ]);
            }else if($request->quantity == 0) {
                $cartItem->delete();
                // dd($cartItem->cart->id);
            }
            return $this->response($cartItem->cart);
        }catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function deleteItems($id) {
        try {
            Cart::findOrFail($id)->cart_items()->delete();
            return $this->response(Cart::find($id));
        }catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

}
