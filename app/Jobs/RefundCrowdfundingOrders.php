<?php

namespace App\Jobs;

use App\Models\CrowdfundingProduct;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefundCrowdfundingOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $crowdfunding;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(CrowdfundingProduct $crowdfunding)
    {
        $this->crowdfunding = $crowdfunding;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //check and ensure if the crowdfunding's status is not 'fail', refund will not happen (this shouldn't happen in theory, only make system more robust,)
        if($this->crowdfunding->status !== CrowdfundingProduct::STATUS_FAIL){
            return;
        }

        //we put the code of handling failed-crowdfunding orders here, because it involves refunding, whihc has interaction with external api(e.g. alipay)
        //it will probalby take a long time to process the refund on (e.g.) alipay server, 
        //so we had better get this task executed in a queued job 
        //and dispatch the job from the scheduled command
        //otherwise, the system cannot conduct a new refund until the previous one finised(crowdfunding usually involves a lot of orders, refund gonna be a heavy job) 
        $orderService = app(OrderService::class);
        //find and get all orders related to this filed crowdfunding, and execute the refund logic
        Order::query()->where('type', Order::TYPE_CROWDFUNDING)//filter for crowdfunding type
                      ->whereNotNull('paid')//filter for those already been paid
                      ->whereHas('items', function($query){
                          //filter for orders with current crowdfunding product
                          $query->where('product_id', $this->crowdfunding->product_id);
                      })
                      ->get()
                      ->each(function(Order $order) use($orderService){//commit refund on each of these order
                          //call refund method
                          $orderService->refundOrder($order);
                      });
    }
}
