<?php

namespace App\Services;

use App\Models\Product;
use App\SearchBuilders\ProductSearchBuilder;

class ProductService
{
    public function getSimilarProductIds(Product $product, $amount){
        //if the product doesnt have any property, just return an empty array
        if(count($product->properties) === 0){
            return [];
        }
        $builder = (new ProductSearchBuilder())->onSale()->paginate($amount, 1);//we gonna choose top $amout number of the result
        //traverse on this product's properties
        foreach($product->properties as $property){
            //add each property to should conditions
            $builder->propertyFilter($property->name, $property->value, 'should');
        }

        //set it at leat has half properties in common with current product for recommended products
        $builder->minShouldMatch(ceil(count($product->properties) / 2));

        //get the params for elasticsearch
        $params = $builder->getParams();

        $params['body']['query']['bool']['must_not'] = [['term' => ['_id' => $product->id]]];

        //do elasticsearch with prepared params
        $result = app('es')->search($params);

        return collect($result['hits']['hits'])->pluck('_id')->all();
    }
}