<?php

namespace App\Console\Commands\Cron;

use App\Jobs\RefundCrowdfundingOrders;
use App\Models\CrowdfundingProduct;
use App\Models\Order;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FinishCrowdfunding extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:finish-crowdfunding';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finish expired crowdfundings';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     parent::__construct();
    // }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //find and get the crowdfundings that are dued, and execute different operations on successful ones and failed ones
        CrowdfundingProduct::query()->where('end_at', '<=', Carbon::now())//filter for crowdfundings which should be ended (expire at and before now)
                                    ->where('status', CrowdfundingProduct::STATUS_FUNDING)//filter for those's status is still in funding
                                    ->get()
                                    ->each(function(CrowdfundingProduct $crowdfunding){//tacke actions on each crowdfunding
                                        //if the total amount collected smaller than target amount, execute fail logic
                                        if($crowdfunding->target_amount > $crowdfunding->total_amount){
                                            $this->crowdfundingFailed($crowdfunding);
                                        }else{
                                            //in other case, call successful logic
                                            $this->crowdfundingSucceed($crowdfunding);
                                        }
                                    });
        // return 0;
    }

    //crowdfunding success logic
    protected function crowdfundingSucceed(CrowdfundingProduct $crowdfunding){
        //set crowdfunding status to 'success'
        $crowdfunding->update([
            'status' => CrowdfundingProduct::STATUS_SUCCESS
        ]);
    }

    //crowdfunding fail logic
    protected function crowdfundingFailed(CrowdfundingProduct $crowdfunding){
        //set crowdfunding status to 'fail'
        $crowdfunding->update([
            'status' => CrowdfundingProduct::STATUS_FAIL
        ]);

        //$orderService = app(OrderService::class);

        //refund involves interacton with alipay server, it gonna take a long time to refund so manay orders(real crowdfunding usaually invloves thousands orders, we cannot let it executed one by one)
        //we we execute the crowdfunding-orders refunding logic in queue job
        dispatch(new RefundCrowdfundingOrders($crowdfunding)); 


        //the following code has been moved to Jobs/RefundCrowdfundingOrders, go and have a look there
        
        //find and get all orders related to this filed crowdfunding, and execute the refund logic
        // Order::query()->where('type', Order::TYPE_CROWDFUNDING)//filter for crowdfunding type
        //               ->whereNotNull('paid')//filter for those already been paid
        //               ->whereHas('items', function($query) use($crowdfunding){
        //                   //filter for orders with current crowdfunding product
        //                   $query->where('product_id', $crowdfunding->product_id);
        //               })
        //               ->get()
        //               ->each(function(Order $order) use($orderService){//commit refund on each of these order
        //                   //call refund method
        //                   $orderService->refundOrder($order);
        //               });  
    }
}
