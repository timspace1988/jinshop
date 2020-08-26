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
                <div class="order-bottom">
                    <div class="order-info">
                        <div class="line"><div class="line-label">Address :&nbsp;</div><div class="line-value">{{ join(' ', $order->address) }}</div></div>
                        <div class="line"><div class="line-label">Remark :&nbsp;</div><div class="line-value">{{ $order->remark ?: '-'  }}</div></div>
                        <div class="line"><div class="line-label">Order no :&nbsp;</div><div class="line-value">{{ $order->no }}</div></div> 
                        <!-- Shipping status -->
                        <div class="line">
                            <div class="line-label">Shippment :&nbsp;</div>
                            <div class="line-value">{{ \App\Models\Order::$shipStatusMap[$order->ship_status] }}</div>
                        </div>
                        <!-- If the order has got shipdata, display it -->
                        @if($order->ship_data)
                            <div class="line">
                                <div class="line-label">Courier :&nbsp;</div>
                                <div class="line-value">{{ $order->ship_data['express_company'] }}</div>
                            </div>
                            <div class="line">
                                <div class="line-label">Shipment no :&nbsp;</div>
                                <div class="line-value">{{ $order->ship_data['express_no'] }}</div>
                            </div>
                        @endif
                    </div>
                    <div class="order-summary text-right">
                        <div class="total-amount">
                            <span>Total amount: </span>
                            <div class="value">${{ $order->total_amount }}</div>
                        </div>
                        <div>
                            <span>Order status: </span>
                            <div class="value">
                                @if($order->paid_at)
                                    @if($order->refund_status === \App\Models\Order::REFUND_STATUS_PENDING)
                                        Paid
                                    @else
                                        {{ \App\Models\Order::refundStatusMap[$order->refund_status] }}
                                    @endif
                                @elseif($order->closed)
                                    Closed
                                @else
                                    Not paid
                                @endif
                            </div>
                        </div>
                        <!-- pay button -->
                        @if(!$order->paid_at && !$order->closed)
                        <div class="payment_buttons">
                            <a href="{{ route('payment.alipay', ['order' => $order->id]) }}" class="btn btn-primary btn-sm">AliPay/支付宝</a>
                        </div>
                        @endif
                        <!-- finish pay button -->

                        <!-- if order status is in-delivery(this project mark this status with delivered), display the received button -->
                        @if($order->ship_status ===\App\Models\Order::SHIP_STATUS_DELIVERED)
                            <div class="receive-button">
                                <form action="{{ route('orders.received', [$order->id]) }}" method="post">
                                    <!-- csrf token -->
                                    {{ csrf_field() }}
                                    <button type="button" id="btn-receive" class="btn btn-sm btn-success">Click to receive</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
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
                axios.post('{{ route('orders.received', [$order->id])}}')
                     .then(function(){
                          //reload the page
                          location.reload();
                      });
            });
        });
    });
</script>
@endsection