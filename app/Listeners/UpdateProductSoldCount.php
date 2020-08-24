<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\OrderItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateProductSoldCount implements ShouldQueue//implements ShouldQueue means this listener will be executed asynchronously(异步执行)
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     //
    // }

    /**
     * Handle the event.
     *
     * @param  OrderPaid  $event
     * @return void
     */
    public function handle(OrderPaid $event)
    {
        //get the order
        $order = $event->getOrder();

        //pre-load
        $order->load('items.product');

        //an iteration on each order items
        foreach($order->items as $item){
            $product = $item->product;

            //calculate the total sold of this product
            //we firstly get all OrderItem related to this product from database and then we filter for the OrderItems which's related order has been paid, finally we sum up all these OrderItems' amout
            $soldCount = OrderItem::query()->where('product_id', $product->id)
                                           ->whereHas('order', function($query){//get the related order with a non-null 'paid_at'
                                               $query->WhereNotNull('paid_at');
                                           })->sum('amount');
            //update this product's sold count
            $product->update([
                'sold_count' => $soldCount,
            ]);
        }
    }
}
