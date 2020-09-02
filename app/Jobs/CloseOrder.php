<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CloseOrder implements ShouldQueue//This job will be put in queue for execute
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order, $delay)
    {
        $this->order = $order;

        //set delay time, the unit of param in delay() is seconds
        $this->delay($delay);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //check if the order has been paid, if paid, do nothing and return,
        if($this->order->paid_at){
            return;
        }

        //if not, we need to close this order, and add the stock back
        //we execute the sql in a tansaction 
        \DB::transaction(function(){
            //change order's 'closed' field from false to true
            $this->order->update(['closed' => true]);

            //do iteration on order's items, find its sku and add amount back to  stock
            foreach($this->order->items as $item){
                $item->productSku->addStock($item->amount);
            } 

            //if this coupon is used on this order, when order closed, we need to add the usage back to total(this coupon's used field - 1)
            if($this->order->couponCode){
                $this->order->couponCode->changeUsed(false);
            }
        });

    }
}
