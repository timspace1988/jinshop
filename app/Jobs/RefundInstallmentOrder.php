<?php

namespace App\Jobs;

use App\Exceptions\InternalException;
use App\Models\Installment;
use App\Models\InstallmentItem;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefundInstallmentOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // return;
        //if this order is not a installment one, not paid or not in refund processing status, we do not execute the rest logics
        if($this->order->payment_method !== 'installment' || !$this->order->paid_at || $this->order->refund_status !== Order::REFUND_STATUS_PROCESSING){
            return;
        }

        //for system robust, we check if we can find this installment in database
        if(!$installment = Installment::query()->where('order_id', $this->order->id)->first()){
            return;
        }

        //traverse on all installment items of current installment
        foreach($installment->items as $item){
            //if the installment item is not paid yet, or the refund status is success and processing, we skip it
            //(for some payment method, e.g. wechat, its refund result is not returned instantly, so we set the status to 'processing' after we execute the refund logic)
            if(!$item->paid_at || in_array($item->refund_status, [InstallmentItem::REFUND_STATUS_SUCCESS, InstallmentItem::REFUND_STATUS_PROCESSING])){
                continue;
            }

            //call refund logic
            try {
                $this->refundInstallmentItem($item);
            }catch(\Exception $e){
                \Log::warning('Installment item refund failed: ' . $e->getMessage(),['installment_item_id' => $item->id,]);

                //even if we got error when processing refund for some installment item, we should not stop, just skip it and continue to process the rest items
                continue;
            }

        }

        //the following checking status codes has been encapsulated into refreshRefundStatus() in Installment model

        // //set the mark of state of all_items_refunded, defaultly set it true, the we do some checking
        // $allSuccess = true;
        // //traverse on all insallment item again
        // foreach($installment->items as $item){
        //     //if the item is paid and its refund status is not success, mark the allSuccess to false, and break
        //     if($item->paid_at && $item->refund_status !== InstallmentItem::REFUND_STATUS_SUCCESS){
        //         $allSuccess = false;
        //         break;
        //     }
        // } 
        // //after all checkings we did when traversing on installments items, if the allSuccess is still true, then we marked the order's refund status as success
        // if($allSuccess){
        //     $this->order->update([
        //         'refund_status' => Order::REFUND_STATUS_SUCCESS,
        //     ]);
        // }

        $installment->refreshRefundStatus();

    }

    //refund logic for installment item, similar to logic of refundOrder method in OrderService.php
    protected function refundInstallmentItem(InstallmentItem $item){
        //refund no for installment item is: order->refund_no + item->sequence
        $refundNo = $this->order->refund_no . '_' . $item->sequence;//order->refund_no is set when we dispatch the RefundInstallmentOrder job in refundOrder method in OrderService.php

        //according to payment method of this installment item, we execute corresponding refund logic
        switch ($item->payment_method) {
            case 'wechat':
                # code...
                break;
            case 'alipay':
                $ret = app('alipay')->refund([
                    'trade_no' => $item->payment_no,//this is same as the alipay out trade no (installment no + item sequence)
                    'refund_amount' => $item->base,//we only refund the base amount, all other fee and extra charge will not be refunded
                    'out_request_no' => $refundNo,//we set it to this installment-item's refund no(order' refund no + intem sequence)
                ]);
                //echo $ret->sub_code;
                //according to alipay document, if the returned data contains sub_code, it indicates the refund is failed
                if($ret->sub_code){
                    $item->update([
                        'refund_status' => InstallmentItem::REFUND_STATUS_FAILED,
                    ]);
                }else{
                    // throw new InternalException('Unknown payment method: ' . $item->payment_method);
                    // break;
                    //dd($ret);
                    //mark this installment item's refund status as success
                    $item->update([
                        'refund_status' => InstallmentItem::REFUND_STATUS_SUCCESS,
                    ]);
                }
                break;
            default:
                //this shoul not happen, the following code is only system's robustness
                throw new InternalException('Unknown payment method: ' . $item->payment_method);
                break;
        }
    }
}
