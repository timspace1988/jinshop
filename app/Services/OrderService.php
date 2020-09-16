<?php

namespace App\Services;

use App\Exceptions\CouponCodeUnavailableException;
use App\Models\Order;
use App\Models\UserAddress;
use App\Models\User;
use Carbon\Carbon;
use App\Models\ProductSku;
use App\Exceptions\InvalidRequestException;
use App\Jobs\CloseOrder;
use App\Models\CouponCode;

class OrderService
{
    //Create an order
    public function store(User $user, UserAddress $address, $remark, $items, CouponCode $coupon = null){
        //if coupon code is passed and not null, we need to firstly check its availability, if doesn't pass, it will throw CouponCodeUnavailableException and go back to precious page before transcaton is executed
        //otherwise, if we leave it after we calculated the order amount, any breach of coupon requirment will end up with a transaction rollback, 
        if($coupon){
            $coupon->checkAvailable($user);//checkAvailable($orderAmount = null), as we haven't got the total amount  for order, we don't pass it here for checking amount requirement
        }


        //Use tansaction to do database operation, if any exception throwed, it will roll back
        $order = \DB::transaction(function() use($user, $address, $remark, $items, $coupon){
            //update address's last used time
            $address->update(['last_used_at' => Carbon::now()]);

            //create an order
            $order = new Order([
                'address' => [
                    'address' => $address->full_address,
                    'zip' => $address->zip,
                    'contact_name' =>$address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark' => $remark,
                'total_amount' => 0, 

            ]);
            //build up relationship with currrent user
            $order->user()->associate($user);

            //write into database
            $order->save();

            $totalAmount =0;
            //Do a iteration on each sku submited by user
            foreach($items as $data){
                $sku = ProductSku::find($data['sku_id']);

                //create an OrderItem and directly get it associate with this order (but not write into  database)
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price' => $sku->price,
                ]);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                //write this order item into database
                $item->save();
                $totalAmount += $sku->price * $data['amount'];
                //decrease this item's sku stock
                if($sku->decreaseStock($data['amount']) <= 0){//decreaseStock($data['amount']) will return the number of affected lines, if it is not a positive num, it means decrease failed
                    throw new InvalidRequestException('This product does not have enough stock');
                }
            }

            // try{

            //from here forward, we have got the total order amount, so we do the coupon available checking again with the orderAmount passed 
            if($coupon){
                
                $coupon->checkAvailable($user, $totalAmount);
                //if passing the checking, the code will continue to execute
                
                //get the new total amount with discount applied
                $totalAmount = $coupon->getAdjustedPrice($totalAmount);
                
                //get order associated with it coupon
                $order->couponCode()->associate($coupon);
                
                //increase the used value of this coupon, and check the returned data
                if($coupon->changeUsed() <=0 ){
                    throw new CouponCodeUnavailableException('Coupon code is used out.');//this usually happened when other customers used out the coupon during we placing the order
                }
                
            }

           


            //Update the total amount of this order
            $order->update(['total_amount' => $totalAmount]);

            //Remove the order items from your cart
            $skuIds = collect($items)->pluck('sku_id')->all();
            //$user->cartItems()->whereIn('product_sku_id', $skuIds)->delete();
            app(CartService::class)->remove($skuIds);
            //we use app() to create a CartService instance, we should avoid using 'new' to create if the class's contructor will be modified and have more params later,
            //because we need to change the params in new CartService(param, param) in every places we created it with 'new'
            //and this store method is called manually by us, not like the store method in controller, which is called by laravel, so we cannot use auto inject here 

            // }catch(\Throwable $t){
            //     return response()->json(['msg' => $t->getMessage()]);
            // }

            return $order;
        });

        //Dispatch the close order job, as we are not in controller, we cannot use $this->dispatch(), indstead, we use dispatch() function directly
        dispatch(new CloseOrder($order, config('app.order_ttl')));
        
        return $order;
    }

    //create an order for crowdfunding product
    public function crowdfunding(User $user, UserAddress $address, ProductSku $sku, $amount){
        //we implement the placing order logic in a transaction
        $order = \DB::transaction(function() use($amount, $sku, $user, $address){
            //update this address's last used at time
            $address->update(['last_used_at' => Carbon::now()]);

            //create an order
            $order = new Order([
                'address' => [
                    'address' => $address->full_address,
                    'zip' => $address->zip,
                    'contact_name' => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark' => '',
                'total_amount' => $sku->price * $amount,
            ]);
            //associate the order to current user
            $order->user()->associate($user);
            //save the order we just created into database
            $order->save();

            //For this application, we designed it be like one order only having one crowdfunding product, that means this order only has one order item
            //now we create the order item and associate it with the product and the sku
            $item = $order->items()->make([
                'amount' => $amount,
                'price' => $sku->price,
            ]);
            //associate the order item with product
            $item->product()->associate($sku->product_id);//associate(param), param could be a model object or its id
            //associate the order item with its sku
            $item->productSku()->associate($sku);
            //save the order item to the database
            $item->save();
            //deduct the amount from the stock on that sku
            if($sku->decreaseStock($amount) <=0){
                throw new InvalidRequestException('This product does not have enough stock');
            }

            return $order;
        });

        //when order is created successfully, we need to dispatch the CloseOrder job
        //calculate the left time by crowdfunding expire time mincing current time
        $crowdfundingTtl = $sku->product->crowdfunding->end_at->getTimestamp() - time();
        //compare the crowdfundingTil value and the default order closure time, choose the smaller one and set it as the CloseOrder job's closure time
        dispatch(new CloseOrder($order, min(config('app.order_ttl'), $crowdfundingTtl)));

        return $order;
    } 
}