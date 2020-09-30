<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InstallmentsController extends Controller
{
    //show installments for user
    public function index(Request $request){
        $installments = Installment::query()->where('user_id', $request->user()->id) 
                                            ->paginate(10);
                            
        return view('installments.index', ['installments' => $installments]);
    }

    //show user's installment details page
    public function show(Installment $installment){
        $this->authorize('own', $installment);

        //Get all installment items for current installment, and sort them by payment sequence
        $items = $installment->items()->orderBy('sequence')->get();
        return view('installments.show', [
                                            'installment' => $installment, 
                                            'items' => $items,
                                            //next unpaid installment item
                                            'nextItem' => $items->where('paid_at', null)->first(),//note: here ->where() is executed on $items collection, not on database
                                        ]);
    }

    //pay installment with alipay
    public function payByAlipay(Installment $installment){
        if($installment->order->closed){
            throw new InvalidRequestException('The order has been closed.');
        }
        if($installment->status === Installment::STATUS_FINISHED){
            throw new InvalidRequestException('The installment has been paid in full.');
        }

        //get current installments's closest outstanding payment(the closest unpaid installment item)
        if(!$nextItem = $installment->items()->whereNull('paid_at')->orderBy('sequence')->first()){
            //if for some reason, there is no upaid installment item (should not happen, this situation should be rulled out in previous status checking)
            throw new InvalidRequestException('The installment has been paid in full.');
        }

        //call alipay 
        return app('alipay')->web([
            //the alipay payment no is the installment no + installment item sequence
            'out_trade_no' => $installment->no . '_' . $nextItem->sequence,
            'total_amount' =>$nextItem->total,
            'subject' => 'Pay ' . config('app.name') . ' installment: ' . $installment->no,
            //The following two callbck urls will overide the corresponding settings in AppServiceProvider(we have differnt callback for installment and normal payment)
            'notify_url' => ngrok_url('installments.alipay.notify'),//ngrok_urll() is in helpers.php, we do the setting for local and production environment there 
            'return_url' => route('installments.alipay.return'),
        ]);
    }

    //alipay callback for front end (browser)
    public function alipayReturn(){
        try {
            app('alipay')->verify();
        } catch (\Exception $e) {
            return view('pages.error', ['msg' => 'Data returned by alipay does not pass verification，but payment might be successful.']);
        }

        return view('pages.success', ['msg' => 'You have made a successful payment.']);
    }

    //alipay callback for back end
    public function alipayNotify(){
        //verify the data sent throw this callback
        $data = app('alipay')->verify();

        //if returned trade status is not success or finished, we will stop executing the rest code
        if(!in_array($data->trade_status, ['TRADE_SUCCESS', 'TRADE_FINISHED'])){
            return app('alipay')->success();//->success() doesn't mean trade is successfull, it only tells alipay server to stop dispatching the callback(otherwise it will dispatches call back regularly) 
        }

        //the alipay payment trade order is: installment no + installment sequence no, so we can get these two number by using explode()
        list($no, $sequence) = explode('_', $data->out_trade_no);

        //for system robust, we do following checks
        if(!$installment = Installment::where('no', $no)->first()){
            return 'fail';
        }
        if(!$item = $installment->items()->where('sequence', $sequence)->first()){
            return 'fail';
        }

        //for some reason, if the current installment item is already paid(e.g. after we update paid_at and other info of current item, alipay continue send us callback data), 
        //we need to tell alipay to stop, and we exit the execution of the rest code(because we have alread executed it as the item is successfully paid)
        if($item->paid_at){
            return app('alipay')->success();
        }

        //after all callback data checking, we execute following code in transaction
        \DB::transaction(function() use($data, $no, $installment, $item){
            //update the installment item being paid
            $item->update([
                'paid_at' => Carbon::now(),
                'payment_method' => 'alipay',
                'payment_no' => $data->trade_no,//alipay out trade no
            ]);

            //if this $item is the first one for this installment, we need to change the installment's status to repaying
            if($item->sequence === 0){
                $installment->update(['status' => Installment::STATUS_REPAYING]);

                //for ordre paid by installment, after we succefully pay the first phase payment, the order's status also need to be changed to paid
                $installment->order->update([
                    'paid_at' => Carbon::now(),
                    'payment_method' => 'installment',
                    'payment_no' => $no,//we also use installment no as order's payment no
                ]);

                //trigger the OrderPaid event
                event(new OrderPaid($installment->order));
            }

            //if $item is the last one for this installment
            if($item->sequence === $installment->count - 1){
                //change this insallment's status to finished
                $installment->update(['status' => Installment::STATUS_FINISHED]);
            }
        });

        return app('alipay')->success();
    }

}
