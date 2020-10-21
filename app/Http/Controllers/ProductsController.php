<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductsController extends Controller
{
    //Products list (get products list through Elasticsearch)
    public function index(Request $request){
        $page = $request->input('page', 1);//defaut page is 1 if user doesn't  specify a page number
        $perPage = 16;//set the product number per page

        //build up the search params
        $params = [
            'index' => 'products',
            'body' => [
                'from' => ($page - 1) * $perPage,//the offset of products number for current page. e.g. the second page's offset is (2-1) * 16 = 16
                'size' => $perPage,
                'query' => [
                    'bool' => [
                        'filter' => [
                            ['term' => ['on_sale' => true]],
                        ],
                    ],
                ],
            ],
        ];

        //if user submit the 'search'
        if($search = $request->input('search', '')){
            //as search could contain multiple words, like Kingston ram, if we set query as 'Kingston ram', it will return us all results matching Kingston or ram, this is not as accurate as we want
            //if we only want the results matching both Kingston and ram simultaneously, we need to set two multi_matches for query 'Kingston' and 'ram' 
            //note: multi_match means it will check the query on multiple fields e.g. description, category, 
            // now , we need to first split the search input, array_filter will filter out the empty value in array
            $keywords = array_filter(explode(' ', $search));
            //dd($keywords);

            $params['body']['query']['bool']['must'] = [];

            //iterate over the keywords and set the multi_match for each of them
            foreach($keywords as $keyword){
                $params['body']['query']['bool']['must'][] = [
                    'multi_match' => [
                        'query' => $keyword,
                        'fields' => [
                            'title^3',
                            'long_title^2',
                            'category^2',
                            'description',
                            'skus_title',
                            'skus_description',
                            'properties_value',
                        ],
                    ],
                ];
            }

            
            //dd($params['body']['query']['bool']['must']);
            
            // $params['body']['query']['bool']['must'] = [
            //     [
            //         'multi_match' => [
            //             'query' => $search,
            //             'fields' => [
            //                 'title^3',
            //                 'long_title^2',
            //                 'category^2',
            //                 'description',
            //                 'skus_title',
            //                 'skus_description',
            //                 'properties_value',
            //             ],
            //         ],
            //     ]
            // ];
        }

        //if user specifies an category
        if($request->input('category_id') && $category = Category::find($request->input('category_id'))){
            //if the category is an directory, we will filter it on the p1: category_path(a strintg containing all ancestor ids) against p2: the specified category's full_category_path(ancestors and itself) 
            //details of this filtering: to  check if p2 is a prefix of p1 (p1 starts with p2) e.g. category_id: 3,  p1: -1-2-3-4-5-6,  p2: -1-2-3-, in this case, p1 will meet the search condition
            if($category->is_directory){
                //for a directory category, well filter it on category_path
                $params['body']['query']['bool']['filter'][] = [
                    'prefix' => ['category_path' => $category->path . $category->id . '-'],
                ];
            }else{
                //if this category is not a directory, we filter it on category_id
                $params['body']['query']['bool']['filter'][] = ['term' => ['category_id' => $category->id]];
            }
        }

        //if user specifies an sorting param, we need to set the sorting orer in $params
        if($order = $request->input('order', '')){
            //firstly check if the $order is ending with _asc or _desc (these are the only sorting orders we accept for all types of sorting:price, sold, rating)
            //for preg_match(p1, p2, $m), e.g. p1='price_asc', p2='_asc', then we got $m[0] is 'price_asc', $m[1] is 'price', $m[2] is 'asc'
            if(preg_match('/^(.+)_(asc|desc)$/', $order, $m)){
                //then need to ensure the sorting param is one of the three legal sorting types in our app:price, sold_count, rating
                //the sorting param should start with one of the follwoing three words
                if(in_array($m[1], ['price', 'sold_count', 'rating'])){
                    //set the sorting param in our $params
                    $params['body']['sort'] = [[$m[1] => $m[2]]];
                }
            }
        }

        //get the products data fron Elasticsearch's 'products' index  using Elasticsearch
        $result = app('es')->search($params);//this result is the documents retrived from the Elasticsearch, not the products from the database yet

        //collect() will covert the array to a collection, pluck() will get values of a given key, here is '_d'
        $productIds = collect($result['hits']['hits'])->pluck('_id')->all();

        //now we get the products from database using $productIds
        $products = Product::query()->whereIn('id', $productIds)
                                    //even though we got ids sorted in Elasticsearch, whereIn() will ignore this order,
                                    //here we can use the sql to sort it against the order in $productIds, orderByRaw('sql') allows us to sort it using an original(raw) sql
                                    ->orderByRaw(sprintf("FIND_IN_SET(id, '%s')", join(',', $productIds)))
                                    //sprintf("FIND_IN_SET(id, '%s)", join(',', [1, 2, 4, 3,])) will ouput a string: "FIND_IN_SET(id, '1,2,4,3')"
                                   ->get();
        //as we do the paginating in Elasticsearch, we cannot use Eloquent's paginate() method here anymore, as paginating returns a LengthAwarePaginator object, 
        //we can create a LengthAwarePaginator object by ourselves so that the front end pages can still render it without any chaning
        $pager = new LengthAwarePaginator($products, $result['hits']['total']['value'], $perPage, $page, [
            //and this is  the base url for products list page
            'path' => route('products.index', false),
        ]);

        //return the products list data for front end
        return view('products.index', [
            'products' => $pager,
            'filters' => [
                'search' => $search,
                'order' => $order,
            ],
            'category' => $category ?? null,//if $category doesn't exist, null will be used
        ]);
    }
    //products list (get products list without going through Elasticsearch)
    //public function index(Request $request, CategoryService $categoryService){
    public function index2(Request $request, CategoryService $categoryService){
        //create a $builder for all query on products for sale
        $builder = Product::query()->where('on_sale', true);
        
        //check if there is parameter in input field named "search", if there is, assing that value to varible $search
        if ($search = $request->input('search', '')){
            //set the search pattern
            $like = '%' . $search . '%';
            //with above pattern, we do fuzz search on product titles, product deatails, SKU titles, SKU descriptions
            $builder->where(function($query)use($like){
                $query->where('title', 'like', $like)
                      ->orWhere('description', 'like', $like)
                      ->orWhereHas('skus', function($query)use($like){
                          $query->where('title', 'like', $like)
                                ->orWhere('description', 'like', $like);
                      });
            });
        }

        //if catetory_id is passed, and there is a corresponding categories record in database
        if($request->input('category_id') && $category = Category::find($request->input('category_id'))){
            //if this is a parent category, we will get all its children's products
            if($category->is_directory){
                //whereHas(param1, function()), param1 is the name of an relationship attribute we gona query against, here it is Product model's categotry attribute 
                $builder->whereHas('category', function($query) use ($category){//$builder, check the top lines of this class
                    $query->where('path', 'like', $category->path.$category->id.'-%');
                    //do a check on products' category attribute(related category object), filter for products with a condition on its related category's path attribute 
                });
            }else{
                //if this is not a parent category, get all products under this category
                $builder->where('category_id', $category->id);
            }
        }

        //Check if there is sorting parameter being selected by customer in 'order' field, if there is, assign value to $order
        if($order = $request->input('order', '')){
            //check if this sorting method ends with _asc or _desc
            if(preg_match('/^(.+)_(asc|desc)$/', $order, $m)){
                //if the sorting method starts with one of the 3 followings, it will be a legal sorting value(method)
                if(in_array($m[1], ['price', 'sold_count', 'rating'])){
                    //build the sorting parameters with the legal parts, and use them to sort
                    $builder->orderBy($m[1],$m[2]);
                }
            }
        }
        
        //$products = Product::query()->where('on_sale', true)->paginate(16);
        $products = $builder->paginate(16);
        return view('products.index', [
            'products' => $products, 
            'filters' =>[
                'search' => $search, 
                'order' => $order
            ],
            'category' => $category ?? null, //this equals to isset($category) ? $category : null, when $category doesn't existm it will return null
            //it is different from   $category ?: null,   when $category doesn't exist, php will report varible doesn't exist error

            //'categoryTree' => $categoryService->getCategoryTree(),//pass the catetory tree to front end page
            //we don't need to pass 'categoryTree' from controller any more, because we have set it being passed automatically by laravel using a ViewComposer class '\App\Http\ViewComposers\CategoryTreeComposer'
            //go check it '\App\Http\ViewComposers\CategoryTreeComposer'
        ]);
    }

    //show product details
    public function show (Product $product, Request $request){
        //check if the selected product is for sale, if not, throw an exception
        if(!$product->on_sale){
            throw new InvalidRequestException('This product is not for sale.');
        }

        //$favored will help us check if we are going to show 'save' or 'remove' button
        $favored = false;
        if($user = $request->user()){//check if user has signed in
            //if user has signed in, search for current product's id in user's saved list
            //boolVal() will convert value to boolean type
            $favored = boolval($user->favoriteProducts()->find($product->id)); 
        }

        //get reviews for this product(actually we get all reviewed order items related to this product here, then pass these order items to blade file)
        $reviews = OrderItem::query()->with(['order.user', 'productSku'])//avoid n + 1
                                     ->where('product_id', $product->id)//all related order item
                                     ->whereNotNull('reviewed_at')//only the ones that have been reviewed
                                     ->orderBy('reviewed_at', 'desc')//sorted with review time from latest to earliest
                                     ->limit(10)//get first 10 records
                                     ->get();

        return view('products.show', ['product' => $product, 'favored' => $favored, 'reviews' => $reviews]);
    }

    //save product interface (will be sent request by ajax)
    public function favor(Product $product, Request $request){
        $user = $request->user();
        //check if user has already saved this product, if yes, then we do nothing
        if($user->favoriteProducts()->find($product->id)){
            return [];
        }

        //use attach() to build relationship between user and product, and save to the relationship table user_favorite_products
        $user->favoriteProducts()->attach($product);//attach() accept an id or the product itself as param
        return [];
    }

    //remove product from saved list
    public function disfavor(Product $product, Request $request){
        $user=$request->user();
        $user->favoriteProducts()->detach($product);
        return [];
    }

    //show saved product list
    public function favorites(Request $request){
        $products = $request->user()->favoriteProducts()->paginate(16);
        return view('products.favorites', ['products' => $products]);
    }
}
