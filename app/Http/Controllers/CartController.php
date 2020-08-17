<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    //add an item to cart
    public function add(AddCartRequest $request){
        $user = $request->user();
        $skuId = $request->input('sku_id');
        $amount = $request->input('amount');
        

        //Check if this item is already in cart against database
        if($cart = $user->cartItems()->where('product_sku_id', $skuId)->first()){
            //if already exists, add up the amount number
            $cart->update([
                'amount' => $cart->amount + $amount,
            ]);
        }else{
            //if not eaxis, create a new cart item 
            $cart = new CartItem(['amount' => $amount]);
            $cart->user()->associate($user);
            $cart->productSku()->associate($skuId);
            $cart->save();
            // try{
            //     $cart->save();
            // }catch(\Throwable $t){
            //     return ['msg' => $t->getMessage()];
            // }
        }

        return [];
    }
}
