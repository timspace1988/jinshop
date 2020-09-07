<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;


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
        
        return view('pages.success', ['msg' => 'You have made a payment successfully']);
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

    //trigger an OrderPaid event
    protected function afterPaid(Order $order){
        event(new OrderPaid($order));
    }
}
