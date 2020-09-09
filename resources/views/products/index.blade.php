@extends('layouts.app')
@section('title', 'Product List')
@section('content')
<div class="row">
    <div class="col-lg-10 offset-lg-1">
        <div class="card">
            <div class="card-body">
                <!-- Search and sort module -->
                <form action="{{ route('products.index') }}" class="search-form">
                    <div class="form-row">
                        <div class="col-md-9">
                            <div class="form-row">
                                <!-- Beginning of breacrumb for catetoies -->
                                <div class="col-auto category-breadcrumb">
                                    <!-- make it start with a link called 'All', which can get you directly to all-produts list  -->
                                    <a href="{{ route('products.index') }}" class="all-products">All</a>
                                    <span>&gt;</span>
                                    <!-- if user reaches current page via choosing a specific category (means a possitive value of $category is passed) -->
                                    @if($category)
                                        <!-- traverse on current category's ancestors -->
                                        @foreach($category->ancestors aS $ancestor)
                                            <!-- add a link named with current ancestor'name -->
                                            <span class="category">
                                                <a href="{{ route('products.index', ['category_id' => $ancestor->id]) }}">{{ $ancestor->name }}</a>
                                            </span>
                                            <span>&gt;</span>
                                        @endforeach
                                        <!-- finally we add current category's name(not a link, because we currently on that page) to the end of the category breadcrumb -->
                                        <span class="category">{{ $category->name }}</span><span></span>
                                        <!-- a hidden input field for current category id, this will ensure the controller still get the current category id when customer change the sort -->
                                        <input type="hidden" name="category_id" value="{{ $category->id }}">
                                    @endif
                                </div>
                                <!-- end of breadcrums for categories -->
                                <div class="col-auto"><input type="text" class="form-control form-control-sm" name="search" placeholder="Search here ..."></div>
                                <div class="col-auto"><button class="btn btn-primary btn-sm">Search</button></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="order" class="form-control form-control-sm flat-right">
                                <option value="">sort</option>
                                <option value="price_asc">Price Low to High</option>
                                <option value="price_desc">Price High to Low</option>
                                <option value="sold_count_desc">Sold High to Low</option>
                                <option value="sold_count_asc">Sold Low to High</option>
                                <option value="rating_desc">Rating High to Low</option>
                                <option value="rating_asc">Rating Low to High</option>
                            </select>
                        </div>
                    </div>
                </form>
                <!-- End of search and sort module -->

                <!-- Beginning of displaying all children categories  -->
                <div class="filters">
                    <!-- if current category is a directory(parent directory) -->
                    @if($category && $category->is_directory)
                        <div class="row">
                            <div class="col-3 filter-key">&nbsp;&nbsp;&nbsp;Shop by category: </div>
                            <div class="col-9 filter-values">
                                <!-- traverse on its children categories -->
                                @foreach($category->children as $child)
                                    <a href="{{ route('products.index', ['category_id' => $child->id]) }}">{{ $child->name }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <!-- End of displaying all children categories -->

                <!-- Products list -->
                <div class="row products-list">
                    @foreach($products as $product)
                        <div class="col-3 product-item">
                            <div class="product-content">
                                <div class="top">
                                    <div class="img"><a href="{{ route('products.show', ['product' => $product->id]) }}"><img src="{{ $product->image_url }}" alt=""></a></div>
                                    <div class="price"><b>$</b>{{ $product->price }}</div>
                                    <div class="title"><a href="{{ route('products.show', ['product' => $product->id]) }}">{{ $product->title }}</a></div>
                                </div>
                                <div class="bottom">
                                    <div class="sold_count"><span>{{ $product->sold_count }} sold</span></div>
                                    <div class="review_count"><span>{{ $product->review_count }}</span> reviews</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="float-right">{{ $products->appends($filters)->render() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scriptsAfterJs')
<script>
    var filters = {!! json_encode($filters) !!};
    $(document).ready(function(){
        //assign previous search and sort parameters to the page when the page is loaded 
        $('.search-form input[name=search]').val(filters.search);
        $('.search-form select[name=order]').val(filters.order);
        //listening on the change of sorting method
        $('.search-form select[name=order]').on('change', function(){
            $('.search-form').submit();
        })
    });
</script>
@endsection