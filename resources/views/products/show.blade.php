@extends('layouts.app')
@section('title', $product->title)

@section('content')
<div class="row">
    <div class="col-lg-10 offset-lg-1">
        <div class="card">
            <div class="card-body product-info">
                <div class="row">
                    <div class="col-5">
                        <img src="{{ $product->image_url }}" alt="" class="cover">
                    </div>
                    <div class="col-7">
                        <div class="title">{{ $product->title }}</div>
                        <div class="price"><label>Price</label><em>$</em><span>{{ $product->price }}</span></div>
                        <div class="sales_and_reviews">
                            <div class="sold_count">Sold <span class="count">{{ $product->sold_count }}</span></div>
                            <div class="review_count">Reviews <span class="count">{{ $product->review_count }}</span></div>
                            <div class="rating" title="Rate {{ $product->rating }}">Rate <span class="count">{{ str_repeat('★', floor($product->rating)) }}{{ str_repeat('☆',5 - floor($product->rating)) }}</span></div>
                        </div>
                        <div class="skus">
                            <label>Select</label>
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                @foreach($product->skus as $sku)
                                    <label class="btn sku-btn" title="{{ $sku->descriptioin }}" data-toggle="tooltip" data-price="{{ $sku->price }}" data-stock="{{ $sku->stock }}" data-placement="bottom">
                                        <input type="radio" name="skus" autocomplete="off" value="{{ $sku->id }}"> {{ $sku->title }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="cart_amount"><label>Quantity</label><input type="text" class="form-control form-control-sm" value="1"><span class="stock"></span></div>
                        <div class="buttons">
                            @if($favored)
                                <button class="btn btn-danger btn-disfavor">Remove from saved</button>
                            @else
                                <button class="btn btn-success btn-favor">❤  Save</button>
                            @endif
                            <button class="btn btn-primary btn-add-to-cart">Add to cart</button>
                        </div>
                    </div>
                </div>
                <div class="product-detail">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a href="#product-detail-tab" class="nav-link active" aria-controls="product-detail-tab" role="tab" data-toggle="tab" aria-selected="true">Description</a>
                        </li>
                        <li class="nav-item">
                            <a href="#product-reviews-tab" class="nav-link" aria-controls="product-reviews-tab" role="tab" data-toggle="tab" aria-selected="false">Reviews</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="product-detail-tab">
                            {!! $product->description !!}
                        </div>
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
        $('[data-toogle="tooltip"]').tooltip({trigger: 'hover'});
        //listening on each sku
        $('.sku-btn').click(function(){
            $('.product-info .price span').text($(this).data('price'));
            $('.product-info .stock').text($(this).data('stock') + ' available');
        });

        //listening on 'Save' button
        $('.btn-favor').click(function(){
            //send a post ajax request to with url generated by route()
            axios.post('{{ route('products.favor', ['product' => $product->id]) }}')
                 .then(
                     //callback function when request succeed
                     function(){
                        swal('Saved', '', 'success').then(function(){
                            location.reload();//refresh page to replacethe save with remove button
                        });
                     },
                     //callback function when request fails
                     function(error){
                        //if the returned code is 401, it means user is not signed in yet
                        if(error.response &&error.response.status ===401){
                            swal('Please sign in to save this item', '', 'error');
                        }else if(error.response && (error.response.data.msg || error.response.data.message)){
                            //if the response data contains msg(might be generated by our customized exception) or message , show msg to user if possible
                            swal(error.response.data.msg ? error.response.data.msg : error.response.data.message, '', 'error');
                        }else{
                            //Other cases, this might be caused by a collapsed system
                            swal('System error', '', 'error');
                        }
                     }
                    );
        });

        //listening on 'Remove from saved' button
        $('.btn-disfavor').click(function(){
            axios.delete('{{ route('products.disfavor', ['product' => $product->id]) }}')
                 .then(
                     function(){
                         swal('Removed', '', 'success').then(function(){
                             location.reload();
                         });
                     }
                 );
        });
    });
</script>
@endsection