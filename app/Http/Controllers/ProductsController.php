<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\OrderItem;
use App\Models\Product;

use Illuminate\Http\Request;

class ProductsController extends Controller
{
    //products list
    public function index(Request $request){
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
                ]
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
