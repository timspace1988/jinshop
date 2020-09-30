<?php

namespace App\Console\Commands\Cron;

use App\Models\Installment;
use App\Models\InstallmentItem;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateInstallmentFine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:calculate-installment-fine';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate the installment overdue charge';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        InstallmentItem::query()->with(['installment'])
                                ->whereHas('installment', function($query){
                                    //filter for all installments which's status is repaying
                                    $query->where('status', Installment::STATUS_REPAYING);
                                })
                                ->where('due_date', '<=', Carbon::now())//filter for installment items which's due date is before now
                                ->whereNull('paid_at')//filter for those havent been paid
                                ->chunkById(1000, function($items){//
                                    //we use chunkById() instead of get(), it will avoid query failure caused by retrieving too many records in one time,
                                    //it will execute the callback function on 1000 records a time, and execute multiple times until all records has been retrieved 

                                    //traverse on all items being retrieved
                                    foreach($items as $item){
                                        //get overdue days
                                        $overdueDays = Carbon::now()->diffInDays($item->due_date);
                                        //add sum of base and fee
                                        $base = big_number($item->base)->add($item->fee)->getValue();

                                        //calculate the fine: amount * overdueDays * fineRate
                                        //we cannot change the order of overdueDays and fineRate, because the fineRate could be a very small decimal, after mutiply with amount, it is still very small and will possibly be rounded to 0, 
                                        //no matter how many days we multiply, the result will always be 0
                                        $fine = big_number($base)->multiply($overdueDays)
                                                                 ->multiply($item->installment->fine_rate)
                                                                 ->divide(100)
                                                                 ->getValue();
                                                                
                                        //Usually, the fine exceeding base payment(base+fee) is not allowed 
                                        //->compareTo() will return -1. 0, 1 for result <, =, >
                                        $fine = big_number($fine)->compareTo($base) === 1 ? $base : $fine;

                                        $item->update(['fine' => $fine]);
                                    }
                                });
       
    }
}
