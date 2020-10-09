<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
//use Str;

class Product extends Model
{
    const TYPE_NORMAL = 'normal';
    const TYPE_CROWDFUNDING = 'crowdfunding';
    public static $typeMap = [
        self::TYPE_NORMAL => 'Normal product',
        self::TYPE_CROWDFUNDING => 'Crowdfunding product',
    ];

    protected $fillable = [
        'title', 'long_title', 'description', 'image', 'on_sale', 'rating', 'sold_count', 'review_count', 'price', 'type',
    ];

    protected $casts = [
        'on_sale' => 'boolean',//change boolean type from 0/1 to false/true when retriving value from database 
    ];

    //relationship with SKU
    public function skus(){
        return $this->hasMany(ProductSku::class);
    }

    //relationship with Category
    public function category(){
        return $this->belongsTo(Category::class);
    }

    //relationship with crowdfunding (CrowdfundingProduct)
    public function crowdfunding(){
        return $this->hasOne(CrowdfundingProduct::class);
    }

    //relationship with ProductProperty
    public function properties(){
        return $this->hasMany(ProductProperty::class);
    }

    //convert image attributes to its absolute path
    //this function will allows to call an attribute 'image_url' e.g. $this->image_url
    public function getImageUrlAttribute(){
        //if the image attribute is already an absolute one (full path), return it directly
        if(Str::startsWith($this->attributes['image'], ['http://', 'https://'])){
            return   $this->attributes['image'];
        }
        return \Storage::disk(env('STORAGE', 'public'))->url($this->attributes['image']);
    } 

    //group the properties with same property name, e.g. some product have versions with different color, so this product has two color attributes: color:blue, color:green 
    public function getGroupedPropertiesAttribute(){
        return $this->properties
         //group the returned ProductProperty instaces by its name attribute, 
         //and put them into a collection, key is the shared property name, value is a collection of all ProductProperty instances containing this shared name attribute        
        ->groupBy('name')
        ->map(function($properties){
            //map method will transform the key-properties collection to key-values collection
            return $properties->pluck('value')->all();
        });
    }

    //transform product model instance into array
    //this will be used when we build up our elasticsearch index, each product will be transformed to a 'document' array, 
    //'document' in Elasticsearch's intex equals to 'row' in database's table
    public function toESArray(){
        //transfrom the model's attributes into array and filter only for fields we need
        $arr = Arr::only($this->toArray(), [//$this->toArray() will transform this model instance to an array
            'id',
            'type',
            'title',
            'category_id',
            'long_title',
            'on_sale',
            'rating',
            'sold_count',
            'review_count',
            'price',
        ]) ;

        //The following attributes need extra work before we saved them into $arr

        //if this model instance has category data, will explode the full name(include its ancestors) of its category into an array, and store it in arr['category], otherwise, store a empty string
        $arr['category'] = $this->category ? explode('-', $this->category->full_name) : '';
        //category path field
        $arr['category_path'] = $this->category ? $this->category->path : '';
        //remove the html tags in description field
        $arr['description'] = strip_tags($this->description);
        //the product could have multiple SKUs and each SKU contains multiple fields, we wil only extract the fileds we need, and do it for each sku, it means the field $arr['skus'] is a two-layer array
        $arr['skus'] = $this->skus->map(function(ProductSku $sku){
            return Arr::only($sku->toArray(), ['title', 'description','price']);
        });
        //do similar to above on product's properties
        $arr['properties'] = $this->properties->map(function(ProductProperty $property){
            return Arr::only($property->toArray(), ['name', 'value']);
        });

        return $arr;
    }

}
