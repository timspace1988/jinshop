@extends('layouts.app')
@section('title', 'Order details')

@section('content')
<div class="row">
    <div class="col-lg-10 offset-lg-1">
        <div class="card">
            <div class="card-header">
                <h4>Order details</h4>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Price</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-right item-amount">Subtotal</th>
                        </tr>
                    </thead>
                    @foreach($order->items as $index => $item)
                        <tr>
                            <td class="product-info">
                                <div class="preview">
                                    <a href="{{ route('products.show', [$item->product_id]) }}" target="_blank">
                                        <img src="{{ $item->product->image_url }}" alt="">
                                    </a>
                                </div>
                                <div>
                                    <span class="product-title">
                                        <a href="{{ route('products.show', [$item->product_id]) }}" target="_blank">{{ $item->product->title }}</a>
                                    </span>
                                    <span class="sku-title">{{ $item->productSku->title }}</span>
                                </div>
                            </td>
                            <td class="sku-price text-center vertical-middle">${{ $item->price }}</td>
                            <td class="sku-amount text-center vertical-middle">{{ $item->amount }}</td>
                            <td class="item-amount text-right vertical-middle">${{ number_format($item->price * $item->amount, 2, '.', '') }}</td>
                        </tr>
                    @endforeach
                    <tr><td colspan="4"></td></tr>
                </table>
                
            </div>
        </div>
    </div>
</div>
@endsection

@section('scriptsAfterJs')
<script>
    $(document).ready(function(){
        //click on received button
        $('#btn-receive').click(function(){
            //Confirm window pops out
            swal({
                title: "Have you received your items?",
                icon: "warning",
                dangerous: true,
                buttons: ['Cancel', 'Yes'],
            }).then(function(ret){
                //if click on cancel, then do nothing
                if(!ret){
                    return;
                }
                //submit the received request using ajax
                axios.post('{{ route("orders.received", [$order->id])}}')
                     .then(function(){
                          //reload the page
                          location.reload();
                      });
            });
        });

        //click on apply for refund button
        $('#btn-apply-refund').click(function(){
            swal({
                text: 'Please enter your reason for refund.',
                content: "input",
                //the 'input' here will be used by following function as param in this script, it's different from $request->input('param') in controller, which is the data sent via post method                
            }).then(function(input){
                //when user hit the  submit button on pop-out window, it will execute this function
                if(!input){
                    swal('You have not enter the reason for refund');
                    return
                }

                //request access for refund interface
                axios.post('{{ route("orders.apply_refund", [$order->id]) }}', {reason: input})
                     .then(function(){
                         swal('Your request for refund is submitted', '', 'success').then(function(){
                             //when user hit the confirm button on pop-out window, reload the page
                             location.reload();
                         });
                     });
            });
        });
    });
</script>
@endsection