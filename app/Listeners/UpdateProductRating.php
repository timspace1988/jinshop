<?php

namespace App\Listeners;

use App\Events\OrderReviewed;
use App\Models\OrderItem;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateProductRating implements ShouldQueue
{
    // /**
    //  * Create the event listener.
    //  *
    //  * @return void
    //  */
    // public function __construct()
    // {
    //     //
    // }

    /**
     * Handle the event.
     *
     * @param  OrderReviewed  $event
     * @return void
     */
    public function handle(OrderReviewed $event)
    {
        //with method help avoid of n + 1 
        $items = $event->getOrder()->items()->with(['product'])->get();//get all order items in the order just got reviewed

        foreach($items as $item){
            $result = OrderItem::query()->where('product_id', $item->product_id)//we use the product_id of order item just reviewed to find all order items with same product_id in database
                                        ->whereNotNull('reviewed_at')//only the ones that have been reviewed
                                        ->whereHas('order', function($query){//only the ones with its relationship 'order' being paid
                                            $query->WhereNotNull('paid_at');
                                        })
                                        ->first([//first([param1, param2]) stands for (select) fields for this sql query, first() means get all fields
                                            DB::raw('count(*) as review_count'),
                                            DB::raw('avg(rating) as rating'),
                                        ]);
                                        /*
                                        notes: laravel will defaultly add '' in sql for params in first([]), e.g. first(['name', 'email]), its sql will be: select 'name', 'email' form xxx,
                                        in this case, if we use first(['count(*) as review_count']) directly,  sql will be: select 'count(*) as review_count' from xxx, this is incorrect,
                                        if we use DB::raw('param'), it will put param's original form into sql: select count(*) as review_count from xxx, this is what we want
                                        the $result of this query will be an object with two attributes: $result->rating, $result->review_count
                                        */
            //update product's average rating and review count
            $item->product->update([
                'rating' => $result->rating,
                'review_count' => $result->review_count,
            ]);
        }

    }
}
