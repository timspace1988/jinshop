<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;
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

    //Show cart
    public function index(Request $request){
        //with(['[productSku.product']), pre-load productSku and product info, which improve sql search efficiency compared to use $item->productSku->product (N+1)
        $cartItems = $request->user()->cartItems()->with(['productSku.product'])->get();

        return view('cart.index',['cartItems' => $cartItems]);
    }

    //remove items from cart
    public function remove(ProductSku $sku, Request $request){
        //$request->user()->cartItem()->where('product_sku_id', $sku->id)->delete();
        try{
            $request->user()->cartItems()->where('product_sku_id', $sku->id)->delete();
        }catch(\Throwable $t){
            return ['m' => $t->getMessage()];
        }
        return [];
    }
}
