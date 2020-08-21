<?php

namespace App\Services;

use Auth;
use App\Models\CartItem;

class CartService
{
    //get cart items 
    public function get(){
        return Auth::user()->cartItems()->with(['productSku.product'])->get();
    }
    
    //add a cart record
    public function add($skuId, $amount){
        $user = Auth::user();

        //Check the database  to see if this product is already in cart, if yes, update the amount(quantity), otherwise, create a new cart record
        if($item = $user->cartItems()->where('product_sku_id', $skuId)->first()){
            $item->update(['amount' => $item->amount + $amount]);
        }else{
            $item = new CartItem(['amount' => $amount]);
            $item->user()->associate($user);
            $item->productSku()->associate($skuId);
            $item->save();
        }

        return $item;
    }

    //remove items from cart
    public function remove($skuIds){
        //This function receive an ID or an array of IDs
        if(!is_array($skuIds)){
            $skuIds = [$skuIds];
        }
        Auth::user()->cartItems()->whereIn('product_sku_id', $skuIds)->delete();
    }
}