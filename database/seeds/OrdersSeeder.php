<?php

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ///get a Faker instance
        $faker = app(Faker\Generator::class);
        //create 100 orders
        $orders = factory(Order::class, 100)->create();
        //the products that have been bought, this is used for the later on update on these products' sold num and rating  
        $products = collect([]);
        foreach($orders as $order){
            //randomly give it 1 to 3 items for each order
            $items = factory(OrderItem::class, random_int(1, 3))->create([
                'order_id' => $order->id,
                'rating' => $order->reviewed ? random_int(1, 5) : null,//git it a random rating  between 1 and 5
                'review' => $order->reviewed ? $faker->sentence : null,
                'reviewed_at' => $order->reviewed ? $faker->dateTimeBetween($order->paid_at) : null,//review at time, from now back to  the paid being paid, can not earlier than paid_at time
            ]);

            //calculate for total price
            $total = $items->sum(function(OrderItem $item){
                return $item->price * $item->amount;
            });

            //if the order uses coupon, calculate the total price coupon discount deducted
            if($order->couponCode){
                $total = $order->couponCode->getAdjustedPrice($total);
            }

            //update order's total price (total_amount field)
            $order->update([
                'total_amount' => $total,
            ]);

            //put(merge) this order's  product items all into the products collection we created before
            $products = $products->merge($items->pluck('product'));//items->pluck('product'), retrieve product attribute (via relationship) of each item from items and put it into an array e.g. User::pluck('id')

        }

        //we have a lot of duplicated products in products collection, because same product sometime appears on different orders, 
        //before we update these products' sold count, rating, reviews count, we need to filter out those duplicated ones, and then do update on each product 
        $products->unique('id')->each(function(Product $product){
            //find out and calculate this product's sold count, rating, reviews count
            $result = OrderItem::query()->where('product_id', $product->id)//find all order items related to this product
                                        ->whereHas('order', function($query){
                                            $query->whereNotNull('paid_at');//filter these order items for the ones have been paid
                                        })
                                        ->first([//first([your query]) will execute the query and return the first result, here it will return an object withe 3 attributes
                                            \DB::raw('count(review) as review_count'),//if the reivew field is null, it will not be counted here
                                            \DB::raw('avg(rating) as rating'),//avg() will not inclue ones if its field value is null
                                            \DB::raw('sum(amount) as sold_count'),
                                        ]);
            
            $product->update([
                'rating' => $result->rating ? : 5, //if a product has not been rated yet, we defaultly mark the rating as 5
                'review_count' => $result->review_count,
                'sold_count' => $result->sold_count,
            ]);
        });
    }
}
