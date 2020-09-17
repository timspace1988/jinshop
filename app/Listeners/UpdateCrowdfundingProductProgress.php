<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateCrowdfundingProductProgress implements ShouldQueue
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
        $order = $event->getOrder();
        //if the order is not a crowdfunding one, no further operation
        if($order->type !== Order::TYPE_CROWDFUNDING){
            return;
        }

        //we will calculate and update the crowdfunding progress for this order's product

        $crowdfunding = $order->items[0]->product->crowdfunding;

        $data = Order::query()->where('type', Order::TYPE_CROWDFUNDING)//filter for crowdfunding orders
                              ->whereNotNull('paid_at')//filter for orders that's already paid
                              ->whereHas('items', function($query) use($crowdfunding){
                                  $query->where('product_id', $crowdfunding->product_id);//filter for orders containing current order's crowdfufnding product
                              })
                              ->first([
                                  //get total amount of these orders
                                  \DB::raw('sum(total_amount) as total_amount'),
                                  //get the number of people who have supported(joined) the crowdfunding, as user can place multiple orders, we need to exclude the dupilicated users(just count them once)
                                  \DB::raw('count(distinct(user_id)) as user_count'),
                              ]);
        
        //update this crowdfunding product's total amount and number of supporting user
        $crowdfunding->update([
            'total_amount' => $data->total_amount,
            'user_count' => $data->user_count,
        ]);
    }
}
