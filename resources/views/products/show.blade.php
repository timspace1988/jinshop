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
                        <!-- start of crowdfunding product module -->
                        @if($product->type === \App\Models\Product::TYPE_CROWDFUNDING)
                            <div class="crowdfunding-info">
                                <div class="have-text">Current amount: </div>
                                <div class="total-amount"><span class="symbol">$ </span>{{ $product->crowdfunding->total_amount }}</div>
                                <!-- bootstrap's progress bar module -->
                                <div class="progress">
                                    <div class="progress-bar progress-bar-success progress-bar-striped" 
                                        role="progressbar" 
                                        aria-valuenow="{{ $product->crowdfunding->percent }}" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100" 
                                        style="min-width: 1em; width: {{ min($product->crowdfunding->percent, 100) }}%">
                                    </div>
                                </div>
                                <div class="progress-info">
                                    <span class="current-progress">Current progress: {{ $product->crowdfunding->percent }} %</span>
                                    <span class="float-right user-count">Supported by {{ $product->crowdfunding->user_count }} people</span>
                                </div>
                                <!-- if this product is still in funding status, we display the prompt info -->
                                @if($product->crowdfunding->status === \App\Models\CrowdfundingProduct::STATUS_FUNDING)
                                    <div>This product need to collect 
                                         <span class="text-red">$ {{ $product->crowdfunding->target_amount }}</span>
                                         before
                                         <span class="text-red">{{ $product->crowdfunding->end_at->format('d-m-Y  H:i:s')}}</span><br>
                                         Crowdfunding ends  
                                         <span class="text-red">{{ $product->crowdfunding->end_at->diffForHumans(now()) }}</span>
                                    </div>
                                @endif
                            </div>
                        <!-- end of crowdfunding module -->
                        @else
                        <!-- start of normal product module -->
                            <div class="price"><label>Price</label><em>$</em><span>{{ $product->price }}</span></div>
                            <div class="sales_and_reviews">
                                <div class="sold_count">Sold <span class="count">{{ $product->sold_count }}</span></div>
                                <div class="review_count">Reviews <span class="count">{{ $product->review_count }}</span></div>
                                <div class="rating" title="Rate {{ $product->rating }}">Rate <span class="count">{{ str_repeat('★', floor($product->rating)) }}{{ str_repeat('☆',5 - floor($product->rating)) }}</span></div>
                            </div>
                        <!-- end of normal product module -->
                        @endif    

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
                            <!-- Crowdfunding product placing order button -->
                            @if($product->type === \App\Models\Product::TYPE_CROWDFUNDING)
                                <!-- check if user is logged in -->
                                @if(Auth::check())
                                    <!-- if still in funding status, display the Join button -->
                                    @if($product->crowdfunding->status === \App\Models\CrowdfundingProduct::STATUS_FUNDING)
                                      <button class="btn btn-primary btn-crowdfunding">Join</button>
                                    @else
                                    <!-- if not in funding status, display the satus info -->
                                      <button class="btn btn-primary disabled">
                                          {{ \App\Models\CrowdfundingProduct::$statusMap[$product->crowdfunding->status] }}
                                      </button>
                                    @endif
                                @else
                                <!-- if have not logged in, display the Sign in button -->
                                    <a href="{{ route('login') }}" class="btn btn-primary">Sign in to join</a>
                                @endif
                            <!-- end of crowdfunding product placing ordr buttton -->
                            @else
                            <!-- Normal product add to cart button -->
                                <button class="btn btn-primary btn-add-to-cart">Add to cart</button>
                            <!-- end of normal product add to cart button -->
                            @endif
                            
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
                            <!-- start of product property -->
                            <div class="properties-list">
                                <div class="properties-list-title">Product property: </div>
                                <ul class="properties-list-body">
                                    @foreach($product->grouped_properties as $name => $values)
                                    <li>{{ $name }}: {{ join(' / ', $values) }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <!-- end of product property -->

                            <!-- product description -->
                            <div class="product-description">
                            {!! $product->description !!}
                            </div>   
                        </div>
                        <!-- start of reviews panel -->
                        <div role="tabpanel" class="tab-pane" id="product-reviews-tab">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <td>User</td>
                                        <td>Item</td>
                                        <td>Rating</td>
                                        <td>Review</td>
                                        <td>Time</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reviews as $review)
                                        <tr>
                                            <td>{{ $review->order->user->name }}</td>
                                            <td>{{ $review->productSku->title }}</td>
                                            <td>{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</td>
                                            <td>{{ $review->review }}</td>
                                            <td>{{ $review->reviewed_at->format('H:i:s - d/m/Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- end of reviews panel -->
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

        //listening on 'Add to cart' button
        $('.btn-add-to-cart').click(function(){
            //send request to 'add to cart' interface(CartController's add method)
            //the second param is a json type, which contains request's attributes(input)
            axios.post('{{ route('cart.add') }}', 
                {
                    sku_id : $('label.active input[name=skus]').val(),
                    amount: $('.cart_amount input').val(),
                })
                .then(
                    //if request succeed, execute this callback
                    function(d){
                        //alert(d.data.msg);
                        swal('Added to you cart.', '', 'success');
                    },
                    //if failed, execute this callback
                    function(error){
                        if(error.response.status === 401){
                            swal('Please sign in to add your item.');
                        }else if(error.response.status === 403){
                            swal('Request refused.', 'Thist might be casued by an unverified account.', 'error');
                        }else if(error.response.status === 422){
                            //422 http status means user's input does not pass the validation
                            var html = '<div>';
                            _.each(error.response.data.errors, function(errors){
                                _.each(errors, function(error){
                                    html += error + '<br>'
                                });
                            });
                            html += '</div>';
                            swal({content : $(html)[0], icon : 'error'});
                        }else{
                            //all other errors should be casued by a collapsed system
                            swal('Syetem error', '', 'error');
                        }
                    }
                );
        });

        //Listening on Join crowdfunding button
        $('.btn-crowdfunding').click(function(){
            //check if use has chosen a SKU
            if(!$('label.active input[name=skus]').val()){
                swal('Please select a product.');
                return;
            }
            //get and put user's addressese into a json type data, and assign it to addresses varible,
            var addresses = {!! json_encode(Auth::check() ? Auth::user()->addresses : []) !!};

            //create a form using jquery
            var $form = $('<form></form>');//you both $form and form, we usually use $ to indicate this is a jquery element

            //add a toggle selector for addresses to the form
            $form.append('<div class="form-group row">' + 
                            '<label class="col-form-label col-sm-3">Select an address</label>' + 
                            '<div class="col-sm-9">' +
                                '<select class="custom-select" name="address_id"></select>' + 
                            '</div>' + 
                         '</div>');

            //traverse on each address, and put them into the address selector
            addresses.forEach(function (address) {
                $form.find('select[name=address_id]')
                     .append("<option value='" + address.id + "'>" +
                                address.full_address + ' ' + address.contact_name + ' ' + address.contact_phone +
                             '</option>');
            });

            //add an input field for purchase number
            $form.append('<div class="form-group row">' +
                            '<label class="col-form-label col-sm-3">Quantity</label>' +
                            '<div class="col-sm-9"><input class="form-control" name="amount"></div>' +
                         '</div>');
            
            //When preaparation is done, call SweetAlert to pop out a window with the form we just created
            swal({
                text: 'Join the crowdfunding',
                content: $form[0],
                buttons: ['Cancel', 'Confirm']
            }).then(function(ret){
                //until clicking the confirm button, no continuous operation will be executed
                if(!ret){
                    return;
                }

                //build up the request
                var req = {
                    address_id: $form.find('select[name=address_id]').val(),
                    amount: $form.find('input[name=amount]').val(),
                    sku_id: $('label.active input[name=skus]').val()
                };

                //call interface of placing crowdfunding order
                axios.post('{{ route("crowdfunding_orders.store") }}', req)
                     .then(function(response){
                         //console.log(response);
                         //alert(response.data.msg);

                         //if order being placed successfully, redirect to order details page
                         swal('Your order has been placed', '', 'success')
                            .then(() => {
                                location.href = '/orders/' + response.data.id;
                            });
                     },
                     function(error){
                         //if the input does not pass the validation, show reasons
                         if(error.response.status === 422){
                             var html = '<div>';
                             _.each(error.response.data.errors, function(errors){
                                 _.each(errors, function(error){
                                     html += error + '<br>';
                                 });
                             });
                             html += '</div>';
                             swal({content: $(html)[0], icon: 'error'});
                         }else if(error.response.status === 403){
                             swal(error.response.data.msg, '', 'error');
                         }else{
                             //console.log(error.response.data.msg);
                             swal('System error', '', 'error');
                         }
                     });
            });

        });
    });
</script>
@endsection