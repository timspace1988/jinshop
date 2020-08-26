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
                    <td rowspan="{{ $order->items->count() + 1 }}">Item list: </td>
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
                    <td>Total amount:</td>
                    <td>$ {{ $order->total_amount }}</td>
                    <!-- ship status -->
                    <td>Shipment status:</td>
                    <td>{{ \App\Models\Order::$shipStatusMap[$order->ship_status] }}</td>
                </tr>
                <!-- ship module -->

                <!-- display the shipment form if order's ship status is pending-->
                @if($order->ship_status === \App\Models\Order::SHIP_STATUS_PENDING)
                    <tr>
                        <td colspan="4">
                            <form action="{{ route('admin.orders.ship', [$order->id]) }}" method="post" class="form-inline">
                                <!-- csrf token -->
                                {{ csrf_field() }}
                                <div class="form-group {{ $errors->has('express_company') ? 'has-error' : '' }}">
                                    <label for="express_company" class="control-label">Courier company</label>
                                    <input type="text" id="express_company" name="express_company" value="" class="form-controll" placeholder="Enter courier name">
                                    @if($errors->has('express_company'))
                                        @foreach($errors->get('express_company') as $msg)
                                            <span class="help-block">{{ $msg }}</span>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="form-group {{ $errors->has('express_no') ? 'has-error' : '' }}">
                                    <label for="express_no" class="control-label">Ship no</label>
                                    <input type="text" id="express_no" name="express_no" value="" class="form-control" placeholder="Enter shipment no">
                                    @if($errors->has('express_no'))
                                        @foreach($errors->get('express_no') as $msg)
                                            <span class="help-block">{{ $msg }}</span>
                                        @endforeach
                                    @endif
                                </div>
                                <button type="submit" class="btn btn-success" id="ship-btn">Deliver</button>
                            </form>
                        </td>
                    </tr>
                    <!-- If not pending, means already shipped, we dispay the info of courier and shipment no -->
                    @else
                        <tr>
                            <td>Courier: </td>
                            <td>{{ $order->ship_data['express_company'] }}</td>
                            <td>Shipment no: </td>
                            <td>{{ $order->ship_data['express_no'] }}</td>
                        </tr>
                @endif
                <!-- end of ship module -->
            </tbody>
        </table>
    </div>
</div>