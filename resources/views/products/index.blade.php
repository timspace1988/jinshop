@extends('layouts.app')
@section('title', 'Product List')
@section('content')
<div class="row">
    <div class="col-lg-10 offset-lg-1">
        <div class="card">
            <div class="card-body">
                <!-- Search and sort module -->
                <form action="{{ route('products.index') }}" class="search-form">
                    <!-- create a hidden field for filters(product property filters) -->
                    <input type="hidden" name="filters">
                    <div class="form-row">
                        <div class="col-md-9">
                            <div class="form-row">
                                <!-- Beginning of bread crumb for catetoies -->
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
                                    <!-- Start of the properties (we selected) bread crumbs -->
                                    @foreach($propertyFilters as $name => $value)
                                        <span class="filter">{{ $name }} :
                                            <span class="filter-value">{{ $value }}</span>
                                            <!-- remove button for a bread crumb -->
                                            <a class="remove-filter" href="javascript: removeFilterFromQuery('{{ $name }}')">x</a>
                                        </span>
                                    @endforeach
                                    <!-- End of the properties bread crumbs -->
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

                <!-- Beginning of displaying all further filter links  -->
                <div class="filters">
                    <!-- if current category is a directory(parent directory) -->
                    <!-- Beginning of displaying all children categories -->
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
                    <!-- End of displaying all children categories -->

                    <!-- Beginning of displaying aggregation links -->
                    @foreach($properties as $property)
                        <div class="row">
                            <!-- display the property name -->
                            <div class="col-3 filter-key">{{ $property['key'] }}:</div>
                            <div class="col-9 filter-values">
                                @foreach($property['values'] as $value)
                                    <a href="javascript: appendFilterToQuery('{{ $property['key'] }}', '{{ $value }}')">{{ $value }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    <!-- End of displaying aggregation links -->
                </div>
                <!-- End of displaying all futher filter links -->

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
    //alert(location.search);
    var filters = {!! json_encode($filters) !!};
    $(document).ready(function(){
        //assign previous search and sort parameters to the page when the page is loaded 
        $('.search-form input[name=search]').val(filters.search);
        $('.search-form select[name=order]').val(filters.order);
        //listening on the change of sorting method
        $('.search-form select[name=order]').on('change', function(){
            //when we change the sorting order, we want keep the property filters we have already selected
            //before we submit the sorting request, we parse the url for current prpoperty filters
            var searches = parseSearch()

            //if we have searches['filters'], we assign the filters string to the hiddent input field 'filters'
            if(searches['filters']){
                $('.search-form input[name=filters]').val(searches['filters']);
            }
            $('.search-form').submit();
        })
    });

    //a function to parse the url for the params, and return it in key-value format
    function parseSearch(){
        //initialise an empty object
        var searches = {};
        //location.search will return the params string from '?' in url e.g. '?search=ram&filters=brand:kingston|size:8GB'
        //substr(1).split('&') will remove the first '?' and split the string on '&', then put them into an array, traverse on each item in it
        location.search.substr(1).split('&').forEach(function(str){
            //after split on '&' each str now is in a key=value form
            //now we split each str on '=', it will be [key, value],
            var result = str.split('=');

            //put them in searches object
            searches[decodeURIComponent(result[0])] = decodeURIComponent(result[1]);
        });

        return searches;
    }

    //a function to build query data using a key-value object
    function buildSearch(searches){
        //initialise a query string
        var query = '?';
        //traverse on searches( each key-value) note: the callback in forEach and _.forEach, we need to use function(value, key), different from function($key, $value) in php
        _.forEach(searches, function(value, key){// e.g. _.forEach(['a', 'b'], function(p1, p2, p3), in first iteration, p1 is 'a'(the value), p2 is 0(the index name), p3 is 'a,b,c'  
            //assemble the query
            query += encodeURIComponent(key) + '=' + encodeURIComponent(value) + '&';
        });
        //remove the '&' at the end
        return query.substr(0, query.length - 1);
    }

    //when user select a further filter e.g. size:8GB, we append the new filter to the url
    function appendFilterToQuery(name, value){
        //parse current url for params
        var searches = parseSearch();

        //if we have already got the 'filters' in searches,
        if(searches['filters']){
            //append the new filter to filters
            searches['filters'] += '|' + name + ':' + value;
        }else{
            //if we don't have a filters in url currentl, initialise a filters now
            searches['filters'] = name + ':' + value;
        }

        //after we got our new searches, we rebuild the search query and set it on url (updte the location.search)
        location.search = buildSearch(searches);
    }

    //remove a property from the property filter query (bread crumb)
    function removeFilterFromQuery(name){
        //parse for current url's query paramter
        var searches = parseSearch();
        //if there is no 'filters', then we do nothing
        if(!searches['filters']){
            return;
        }

        //initialise an empty filter array
        var filters = [];
        //split the filters string on '|' and traverse on each item
        searches['filters'].split('|').forEach(function(filter){
            //split each item (name:value) on ':'
            var result = filter.split(':');
            //check the name of the item, if it is not the one we clicked to remove, just put it into the new filters array we previously created, 
            //if it is the one we want to remove, then do nothing but exit(return)
            if(result[0] === name){
                return;
            }
            filters.push(filter);
        });

        //set search['filters'] with new filters array, 
        searches['filters'] = filters.join('|');

        //then rebuild the query and triiger the browser to redirect to new page
        location.search = buildSearch(searches);
    }

</script>
@endsection