<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'title', 'description', 'image', 'on_sale', 'rating', 'sold_count', 'review_count', 'price', 'type',
    ];

    protected $cast = [
        'on_sale' => 'boolean',//change boolean type from 0/1 to false/true when retriving vale from database 
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

    //convert image attributes to its absolute path
    //this function will allows to call an attribute 'image_url' e.g. $this->image_url
    public function getImageUrlAttribute(){
        //if the image attribute is already an absolute one (full path), return it directly
        if(Str::startsWith($this->attributes['image'], ['http://', 'https://'])){
            return   $this->attributes['image'];
        }
        return \Storage::disk(env('STORAGE', 'public'))->url($this->attributes['image']);
    } 
}
