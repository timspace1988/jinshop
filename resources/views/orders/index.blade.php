@extends('layouts.app')
@section('title', 'Order list')

@section('content')
<div class="row">
    <div class="col-lg-10 offset-lg-1">
        <div class="card">
            <div class="card-header">Order list</div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($orders as $order)
                        <li class="list-group-item">
                            <div class="card">
                                <div class="card-header">
                                    Order number: {{ $order->no }} <span class="float-right">{{ $order->created_at->format('d-m-Y H:i:s') }}</span>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Items</th>
                                                <th class="text-center">Price</th>
                                                <th class="text-center">Quantity</th>
                                                <th class="text-center">Total price</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Options</th>
                                            </tr>
                                        </thead>
                                        @foreach($order->items as $index => $item)
                                            <tr>
                                                <td class="product-info">
                                                    <div class="preview">
                                                        <a href="{{ route('products.show', [$item->product_id]) }}" target="_blank">
                                                            <img src="{{ $item->product->image_url }}">
                                                        </a>
                                                    </div>
                                                    <div>
                                                        <span class="product-title">
                                                            <a href="{{ route('products.show', [$item->product_id]) }}">{{ $item->product->title }}</a>
                                                        </span>
                                                        <span class="sku-title">{{ $item->productSku->title }}</span>
                                                    </div>
                                                </td>
                                                <td class="sku-price text-center">$ {{ $item->price }}</td>
                                                <td class="sku-amount text-center">{{ $item->amount }}</td>
                                                <!-- when an order contains multiple items, only ouput total amount, status, options for one row -->
                                                @if($index === 0)
                                                    <td rowspan="{{ count($order->items) }}" class="text-center total amount">$ {{ $order->total_amount }}</td>
                                                    <td rowspan="{{ count($order->items) }}" class="text-center">
                                                        @if($order->paid_at)
                                                            @if($order->refund_status === \App\Models\Order::REFUND_STATUS_PENDING)
                                                                Paid
                                                            @else
                                                                {{ \APP\Models\Order::$refundStatusMap[$order->refund_status] }}
                                                            @endif
                                                        @elseif($order->closed)
                                                            Closed
                                                        @else
                                                            Not paid <br>
                                                            Please make payment before {{ $order->created_at->addSeconds(config('app.order_ttl'))->format('d-m-Y H:i:s') }}<br>
                                                            Otherwise, your order will be closed.
                                                        @endif
                                                    </td>
                                                    <td rowspan="{{ count($order->items) }}" class="text-center"><a href="{{ route('orders.show', ['order' => $order->id]) }}" class="btn btn-primary btn-sm">Details</a></td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="float-right">{{ $orders->render() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection