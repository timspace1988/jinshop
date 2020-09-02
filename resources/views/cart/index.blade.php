@extends('layouts.app')
@section('title', 'Cart')

@section('content')
<div class="row">
    <div class="col-lg-10 offset-lg-1">
        <div class="card">
            <div class="card-header">My cart</div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>Items</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Options</th>
                        </tr>
                    </thead>
                    <tbody class="product_list">
                        @foreach($cartItems as $item)
                            <tr data-id="{{ $item->productSku->id }}">
                                <td>
                                    <input type="checkbox" name="select" value="{{ $item->productSku->id }}" {{ $item->productSku->product->on_sale ? 'checked' : 'disabled' }}>
                                </td>
                                <td class="product_info">
                                    <div class="preview">
                                        <a href="{{ route('products.show', [$item->productSku->product_id]) }}">
                                            <img src="{{ $item->productSku->product->image_url }}" alt="">
                                        </a>
                                    </div>
                                    <div @if(!$item->productSku->product->on_sale) class="not_on_sale" @endif>
                                        <span class="product_title">
                                            <a href="{{ route('products.show', [$item->productSku->product_id]) }}" target="_blank">{{ $item->productSku->product->title }}</a>
                                        </span>
                                        <span class="sku_title">{{ $item->productSku->title }}</span>
                                        @if(!$item->productSku->product->on_sale)
                                            <span class="warning">This product is no longer for sale.</span>
                                        @endif
                                    </div>
                                </td>
                                <td><span class="price">${{ $item->productSku->price }}</span></td>
                                <td>
                                    <input type="text" class="form-control form-control-sm amount" @if(!$item->productSku->product->on_sale) disabled @endif name="amount" value="{{ $item->amount }}">
                                </td>
                                <td><button class="btn btn-sm btn-danger btn-remove">Remove</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <!--remark and address select-->
                <div>
                    <form class="form-horizontal" role="form" id="order-form">
                    <div class="form-group row">
                            <label class="col-form-label col-sm-3 text-md-right">Select an address</label>
                            <div class="col-sm-9 col-md-7">
                                <select name="address" class="form-control">
                                    @foreach($addresses as $address)
                                        <option value="{{ $address->id }}">{{ $address->full_address }}{{ $address->contact_name }}{{ $address->contact_phone }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-sm-3 text-md-right">Message for seller</label>
                            <div class="col-sm-9 col-md-7">
                                <textarea name="remark" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <!-- coupon section -->
                        <div class="form-group row">
                            <label class="col-form-label col-sm-3 text-md-right">Coupon</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="coupon_code">
                                <span class="form-text text-muted" id="coupon_desc"></span>
                            </div>
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-success" id="btn-check-coupon">Apply</button>
                                <button type="button" class="btn btn-danger" style="display: none;" id="btn-cancel-coupon">Remove</button>
                            </div>
                        </div>
                        <!-- end of coupon section -->
                        <div class="form-group">
                            <div class="offset-sm-3 col-sm-3">
                                <button type="button" class="btn btn-primary btn-create-order">Place order</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scriptsAfterJs')
<script>
    $(document).ready(function(){
        //listening on remove button
        $('.btn-remove').click(function(){
            var id = $(this).closest('tr').data('id');//$(this) is the current jquery object
            swal(
                {
                    title: "Do you want to remove this item?",
                    icon: "warning",
                    buttons: ['Cancel', 'Yes'],
                    dangerMode:true,
                }
            )
            .then(function(willDelete){
                //when remove is clicked, willDelete value will become true, otherwise will be false
                if(!willDelete){
                    return;
                }
                axios.delete('/cart/' + id)
                    //  .then(function(d){
                    //      console.log(d.data.m);
                    //  })
                     .then(function(){
                         location.reload();
                     });
            });
        });

        //listening on select/de-select all checkbox,
        $('#select-all').change(function(){
            //.prop('checked') will check if a jquery element tag contains a 'checked' property
            var checked = $(this).prop('checked');
            //get all checkbox with 'name=select' and without 'disabled' property
            $('input[name=select][type=checkbox]:not([disabled])').each(function(){
                //set their 'checked' property same as the 'select/de-select all' checkbox
                $(this).prop('checked', checked);
            });
        });

        //listening on place order button
        $('.btn-create-order').click(function(){
            //build up the request params
            var req = {
                address_id: $('#order-form').find('select[name=address]').val(),
                items: [],
                remark: $('#order-form').find('textarea[name=remark]').val(),
                coupon_code: $('input[name=coupon_code]').val(),//get coupon code from input
            };
            //Do iteration on all <tr> tag with data-id attribute within <table>, which is every product sku in this cart
            $('table tr[data-id]').each(function(){
                //get the checkbox in current line
                var $checkbox = $(this).find('input[name=select][type=checkbox]');
                //if this checkbox is disabled or unchecked, we skip it
                if($checkbox.prop('disabled') || !$checkbox.prop('checked')){
                    return;
                }
                //get the amount input
                var $input = $(this).find('input[name=amount]');
                //if user set this line's amount as 0 or a non-numeric data, we skip it
                if($input.val() == 0 || isNaN($input.val())){
                    return;
                }
                //put sku id and its amount  in request array
                req.items.push({
                    sku_id: $(this).data('id'),
                    amount: $input.val(),
                });
            });
            axios.post('{{ route("orders.store") }}', req)
                 .then(
                     function(response){
                        //alert(response.data.msg);
                        swal('Your order has been placed.', '', 'success')
                        .then(function(){
                            //location.reload();
                            location.href = '/orders/' + response.data.id;
                        });
                        
                    },
                    function(error){
                        if(error.response.status === 422){
                            //422 means the inputs does not pass the validation
                            var html = '<div>';
                            _.each(error.response.data.errors, function(errors){
                                _.each(errors, function(error){
                                    html += error + '<br>';
                                })
                            });
                            html += '</div>';
                            swal({content: $(html)[0], icon: 'error'});
                        }else if(error.response.status === 403){
                            swal(error.response.data.msg);
                        }else{
                            //console.log(error.response);
                            //Other errors might be caused by system collapse
                            swal('System error', '', 'error');
                        }
                    }
                 );
        });

        //Click on coupon 'Apply' button
        $('#btn-check-coupon').click(function(){
            //get the code user just entered
            var code = $('input[name=coupon_code]').val();

            //if customer doesn't enter any coupon code, an window propt will poped out
            if(!code){
                swal('Pleas enter your coupon code', '', 'warning');
                return;
            }

            //call apply interface, send request to show method in CouponCodesController(customer one)
            axios.get('/coupon_codes/' + encodeURIComponent(code))
                  //if we do not use encodeURIComponent() and the code is test/test, url will be /coupon_codes/test/test, here it will be /coupon_codes/test%2Ftest
                 .then(
                     //first param in then is a call-back for successful request
                     function(response){
                        //console.log(response);
                        //alert(response.data.description);
                        //return;
                        $('#coupon_desc').text(response.data.description);//display discount info(description is an attribute we create in CouponCode model)
                        $('input[name=coupon_code]').prop('readonly', true);//set code input field read only
                        $('#btn-cancel-coupon').show();//set cancel button visiable
                        $('#btn-check-coupon').hide()//set apply button invisiable;
                     },
                     //second param in then is the call-back if any error occors
                     function(error){
                         //if http status is 404, it means the coupon doesn't exist or not enabled
                         if(error.response.status === 404){
                             swal('The coupon does not exist.', '', 'error');
                         }else if(error.response.status === 403){
                             //if status is 403, it means there are other conditions not met
                             swal(error.response.data.msg, '', 'error');
                         }else{
                             console.log(error.response);
                             //return;
                             //other errors
                             swal('System error.', '', 'error');
                         }
                     }
                 );
        });

        //Click cancel button on coupon section
        $('#btn-cancel-coupon').click(function(){
            $('#coupon_desc').text('');//clear discount info
            $('input[name=coupon_code]').prop('readonly', false);//re-activate the coupon input area
            $('#btn-cancel-coupon').hide();//set cancel button invisible
            $('#btn-check-coupon').show();//set apply button visible again
        });
    });
</script>
@endsection