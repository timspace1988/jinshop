<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Installment;
use App\Models\InstallmentRate;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    //
    public function payByAlipay(Order $order, Request $Request ){
        //check if the order belongs to current user
        $this->authorize('own', $order);
        //if order is paid or closed
        if($order->paid_at || $order->closed){
            throw new InvalidRequestException('You cannot  make payment, please check order status.');
        }

        // try{

        //call aplipay's web page payment
        return app('alipay')->web([
            'out_trade_no' => $order->no,//order no is important, we must  ensure it is unique(check order model how we generate order no)
            'total_amount' => $order->total_amount,//support two digits after dot
            'subject' => 'Payment to jinshop for order: ' . $order->no,
            //'timeout_express' => '30m'//alipay payment expires in 30 minutes
        ]);

        // }catch(\Throwable $t){
        //     echo $t->getMessage();
        //     dd($t);
        // }
    }
    //callback for front-end after payment
    public function alipayReturn(){
        //Verify the data returned by alipay and get us the data after being parsed
        try {
            $data = app('alipay')->verify();
            //dd($data);
        } catch (\Exception $e) {
            return view('pages.error', ['msg' => 'Data returned by alipay does not pass verification，but payment might be successful.']);
        }
        
        return view('pages.success', ['msg' => 'You have made a successful payment.']);
        //return redirect(route('orders.index'));
    }

    //callback for back-end after payment, 
    //front-end callback will highly depend on browser, we cannot trust it, so we check if payment is successful based on back-end callback
    public function alipayNotify(){
        $data = app('alipay')->verify();

        //If your order's payment status is not sucecess or finished, we should let alipay stop it here 
        if(!in_array($data->trade_status, ['TRADE_SUCCESS', 'TRADE_FINISHED'])){
            //success() does not mean payment is successfull, it just sends data to alipay to tell it we have processed the order and do not keep calling this callback
            //otherwise, alipay will return data every fixed time span
            return app('alipay')->success();
        }
        //alipay sends this request to our back-end server and we cannot use dd to see data on page, so we write it down to logs
        //\Log::debug('Alipay notify', $data->all());

        //$data->out_trade_no will get order no from data returned by alipay and then we use order no to get the order from database
        $order = Order::where('no', $data->out_trade_no)->first();

        //Do this check to make system more robust, making succesful payment to an order not existing is rarely happened
        if(!$order){
            return 'fail';
        }

        //If for some reason, the status of order we retrived from  database is already paid, we also need to tell alipay to stop executing callback
        if($order->paid_at){
            return app('alipay')->success();
        }

        //update this order's status(info)
        $order->update([
            'paid_at' => Carbon::now(),
            'payment_method' => 'alipay',
            'payment_no' => $data->trade_no,//alipay order payment number
        ]);

        $this->afterPaid($order);//trigger OrderPaid event after payment is successful

        return app('alipay')->success();

    }

    //create a installment payment interface
    public function payByInstallment(Order $order, Request $request){
        //try{

        //check if current order belongs to current user
        $this->authorize('own', $order);

        //if order is already paid or closed
        if($order->paid_at || $order->closed){
            throw new InvalidRequestException('Incorrect order status.');
        }

        //if order doesn't meet minimum installment amount requirement
        //$installmentRate = InstallmentRate::query()->first();
        if($order->total_amount < config('app.min_installment_amount')){//installment rates config is set in AppServiceProvider.php
            throw new InvalidRequestException('Order amount is less than minimum installment amount.');

        }

        //check the installments phases submited by user, make sure it must be a number among our phases settings
        $this->validate($request, [
            'count' => ['required', Rule::in(array_keys(config('app.installment_fee_rate')))],
        ]);

        //as one order can only establish one installment, if for some reasons, there is already other installment for this order appears in database, and its status is pending(not paid yet), 
        //we need to delete these installments before we create this one
        Installment::query()->where('order_id', $order->id)
                            ->where('status', Installment::STATUS_PENDING)
                            ->delete();
        $count = $request->input('count');

        //Now, after all checks ans preparation, we create a new Instalment instance
        $installment = new Installment([
            'total_amount' => $order->total_amount,
            //number of installment phases(3, 6, or 12)
            'count' => $count,
            'fee_rate' => config('app.installment_fee_rate')[$count],
            'fine_rate' => config('app.installment_fine_rate'),
        ]);

        //associate with relationship model
        $installment->user()->associate($request->user());
        $installment->order()->associate($order);
        $installment->save();
        
        //the expire date for first (phase) installment is tomorrow 0 am
        $dueDate = Carbon::tomorrow(); 

        //calculate the base payment for each installment phase (doesn't include any fee and fine)
        $base = big_number($order->total_amount)->divide($count)->getValue();

        //calculate the fee for each installment phase
        $fee = big_number($base)->multiply($installment->fee_rate)->divide(100)->getValue();

        //create installment items according to the phase number customer chose
        for($i = 0; $i < $count; $i++){
            //for the last installment payment, the $base maybe not accurate because it has some fraction rouded, so we need to let total amount minus previous installments payments
            if($i == $count - 1){
                $base = big_number($order->total_amount)->subtract(big_number($base)->multiply($count - 1));
            }

            //crate the installment item
            $installment->items()->create([
                'sequence' => $i,
                'base' => $base,
                'fee' => $fee,
                'due_date' => $dueDate,
            ]);

            //add 30 days to previous payment's due date
            $dueDate = $dueDate->copy()->addDays(30);
        }

        return $installment;

        // }catch(\Throwable $t){
        //     return ['msg' => $t->getMessage()];
        // }

    }

    //trigger an OrderPaid event
    protected function afterPaid(Order $order){
        event(new OrderPaid($order));
    }
}
