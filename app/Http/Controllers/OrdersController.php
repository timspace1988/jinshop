<?php

namespace App\Http\Controllers;


use App\Exceptions\InvalidRequestException;
use App\Http\Requests\OrderRequest;
use App\Jobs\CloseOrder;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\UserAddress;
use App\Services\CartService;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    //Create order
    //Note: $request can only appear in controler and middleware, do not put in a package class
    public function store(OrderRequest $request, OrderService $orderService){
        $user = $request->user();

        $address = UserAddress::find($request->input('address_id'));

        // try{
        //     return $orderService->store($user, $address, $request->input('remark'), $request->input('items'));
        // }catch(\Throwable $t){
        //     return ['msg' => $t->getMessage()];
        // }

        return $orderService->store($user, $address, $request->input('remark'), $request->input('items'));

        /*

        //
        //open a database affair using \DB::tansaction(), all sql operations in the callback function will be included in this affair
        //if any exception is throwed by this callback, the whole of this affair will be rolled back, otherwise it will submit this affair to database
        //

        try{

        $order = \DB::transaction(function() use($user, $request, $cartService){
            //Get the address select by user, and update its last used time
            $address = UserAddress::find($request->input('address_id'));
            $address->update(['last_used_at' => Carbon::now()]);
            //create an order
            $order = new Order([
                //put the selected address in an array which will be saved as a json type data into database
                'address' => [
                    'address' => $address->full_address,
                    'zip' => $address->zip,
                    'contact_name' => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark' => $request->input('remark'),
                'total_amount' => 0,
            ]);
            //associate the order with current user
            $order->user()->associate($user);
            //write into database
            $order->save();

            $totalAmount = 0;
            $items = $request->input('items');
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

            //Update the total amount of this order
            $order->update(['total_amount' => $totalAmount]);

            //Remove the order items from your cart
            $skuIds = collect($items)->pluck('sku_id')->all();
            //$user->cartItems()->whereIn('product_sku_id', $skuIds)->delete();
            $cartService->remove($skuIds);
            
            return $order;
        });

        }catch(\Throwable $t){
            return ['msg' => $t->getMessage()];
        }

        //after an order is created(placed), we need to trigger a CloseOrder job (close order after some time if not paid) and dispatch it into queue
        $this->dispatch(new CloseOrder($order, config('app.order_ttl')));
        return $order;

        */
    }

    //show order list for customer
    public function index(Request $request){
        $orders = Order::query()->with(['items.product', 'items.productSku'])//avoid N+1 problem
                               ->where('user_id', $request->user()->id)
                               ->orderBy('created_at', 'desc')
                               ->paginate();
        
        return view('orders.index', ['orders' => $orders]);
    }

    //order details page
    public function show(Order $order, Request $request){
        //Only order's owner can see his order's details
        $this->authorize('own', $order);
        return view('orders.show', ['order' => $order->load(['items.product', 'items.productSku'])]);
    }
}
