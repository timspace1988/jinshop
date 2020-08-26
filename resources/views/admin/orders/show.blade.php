<div class="box box-info">
    <div class="box-header with-box">
        <h3 box-title>Order no: {{ $order->no }}</h3>
        <div class="box-tools">
            <div class="box-group float-right" style="margin-right: 10px;">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-default"><i class="fa fa-list"></i>List</a>
            </div>
        </div>
    </div>
    <div class="box-body">
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <td>Customer: </td>
                    <td>{{ $order->user->name }}</td>
                    <td>Paid at: </td>
                    <td>{{ $order->paid_at->format('H:i:s - d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>Payment method: </td>
                    <td>{{ $order->payment_method }}</td>
                    <td>Payment no: </td>
                    <td>{{ $order->payment_no }}</td>
                </tr>
                <tr>
                    <td>Address: </td>
                    <td colspan="3">{{ $order->address['address'] }} {{ $order->address['zip'] }} {{ $order->address['contact_name'] }} {{ $order->address['contact_phone'] }}</td>
                </tr>
                <tr>
                    <td rowspan="{{ $order->items->count() + 1 }}">Item list</td>
                    <td>Item name</td>
                    <td>Price</td>
                    <td>Quantity</td>
                </tr>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->title }} {{ $item->productSku->title }}</td>
                        <td>$ {{ $item->price }}</td>
                        <td>{{ $item->amount }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td>Total amount</td>
                    <td colspan="3">$ {{ $order->total_amount }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>