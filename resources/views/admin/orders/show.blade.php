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
                    <!-- but we have an exception: if admin has approved refund, there will be no need to send shipment, it measn we don't need shipement form here, so we need to exclude that situation here -->
                    @if($order->refund_status !== \App\Models\Order::REFUND_STATUS_SUCCESS)
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
                    @endif
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

                <!-- if refund status is not pending, display the refund handle panel(and button) -->
                @if($order->refund_status !== \App\Models\Order::REFUND_STATUS_PENDING)
                    <tr>
                        <td>Refund status: </td>
                        <td colspan="2">{{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}  -  Reason: {{ $order->extra['refund_reason'] }}</td>
                        <!-- if order's refund status is applied, display the refund handle button -->
                        @if($order->refund_status === \App\Models\Order::REFUND_STATUS_APPLIED)
                            <td>
                                <button class="btn btn-sm btn-success" id="btn-refund-agree">Approve</button>
                                <button class="btn btn-sm btn-danger" id="btn-refund-disagree">Decline</button>
                            </td>
                            
                        @endif
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>


<script>
    $(document).ready(function(){
        //click decline button on refund handle panel
        $('#btn-refund-disagree').click(function(){
            //Note: the version of SweetAlert (swal) used in laravel-admin is different from what we used in front end page, so the attributes are also different
            swal({
                title: 'Please enter the reason for decline',
                input: 'text',
                showCancelButton: true,
                confirmButtonText: "Confirm",
                cancelButtonText: "Cancel",
                showLoaderOnConfirm: true,
                preConfirm: function(inputValue){
                    if(!inputValue){
                        swal('Decline reason cannot be empty', '', 'error');
                        return false;
                    }
                    //laravel-admin doesn't have axios, so here we use jQuery's ajax() to send our ajax request
                    //try{

                    return $.ajax({
                        url: '{{ route("admin.orders.handle_refund", [$order->id]) }}',
                        type: 'POST',
                        data: JSON.stringify({//convert data into JSON string
                            'agree': false,//without '' just use agree: false is ok
                            'reason': inputValue,//same with above
                            //CSRF token, in laravel-admin, we can use LA.token to get a CSRF token
                            '_token': LA.token,//same with above
                        }),
                        contentType: 'application/json; charset=utf-8',//Request's data type JSON
                        success: function(d){
                            console.log(d);
                        },
                        // dataType: 'json',
                    });

                    // } catch(e){
                    //     console.log(e);
                    // }
                },
                allowOutsideClick: false
            }).then(function(ret){
                //if admin click on cancel button, we do nothing
                if(ret.dismiss === 'cancel'){
                    return;
                }
                swal({
                    title: 'Operation successful',
                    type: 'success'
                }).then(function(){
                    //when user hit the ok button on pop-out notification window, reload the page
                    location.reload();
                });
            });
        }); 

        //click on approve button in refund handle panel
        $('#btn-refund-agree').click(function(){
            swal({
                title: 'Do you approve the refund?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                showLoaderOnConfirm: true,
                preConfirm: function(){
                    return $.ajax({
                        url: '{{ route("admin.orders.handle_refund", [$order->id]) }}',
                        type: 'POST',
                        data: JSON.stringify({
                            agree: true,//agree to refund
                            _token: LA.token,
                        }),
                        //dataType: 'json',
                        contentType: 'application/json',
                        // success: function(d){
                        //     //alert(d.view_data);
                        //     console.log(d);
                        //     //return;
                        //     alert(d);
                        // },
                    });
                },
                allowOutsideClick: false,
            }).then(function(ret){
                //alert(ret.data);
                //if user hit cancel button, do nothing()
                if(ret.dismiss === 'cancel'){
                    return;
                }
                swal({
                    title: 'Operation successful',
                    type: 'success',
                }).then(function(){
                    //if user hit ok button on swal window, reload the page
                    location.reload();
                });
            });
        });
    });
</script>

<!-- <script>
$(document).ready(function() {
  // 不同意 按钮的点击事件
  $('#btn-refund-disagree').click(function() {
    // Laravel-Admin 使用的 SweetAlert 版本与我们在前台使用的版本不一样，因此参数也不太一样
    swal({
      title: '输入拒绝退款理由',
      input: 'text',
      showCancelButton: true,
      confirmButtonText: "确认",
      cancelButtonText: "取消",
      showLoaderOnConfirm: true,
      preConfirm: function(inputValue) {
        if (!inputValue) {
          swal('理由不能为空', '', 'error')
          return false;
        }
        // Laravel-Admin 没有 axios，使用 jQuery 的 ajax 方法来请求
        return $.ajax({
          url: '{{ route('admin.orders.handle_refund', [$order->id]) }}',
          type: 'POST',
          data: JSON.stringify({   // 将请求变成 JSON 字符串
            agree: false,  // 拒绝申请
            reason: inputValue,
            // 带上 CSRF Token
            // Laravel-Admin 页面里可以通过 LA.token 获得 CSRF Token
            _token: LA.token,
          }),
          contentType: 'application/json',  // 请求的数据格式为 JSON
        });
      },
      allowOutsideClick: false
    }).then(function (ret) {
      // 如果用户点击了『取消』按钮，则不做任何操作
      if (ret.dismiss === 'cancel') {
        return;
      }
      swal({
        title: '操作成功',
        type: 'success'
      }).then(function() {
        // 用户点击 swal 上的按钮时刷新页面
        location.reload();
      });
    });
  });
});
</script> -->