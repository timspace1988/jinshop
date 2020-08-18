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
    });
</script>
@endsection