<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'description', 'image', 'on_sale', 'rating', 'sold_count', 'review_count', 'price'
    ];

    protected $cast = [
        'on_sale' => 'boolean',//change boolean type from 0/1 to false/true when retriving vale from database 
    ];

    //relationship with SKU
    public function skus(){
        return $this->hasMany(ProductSku::class);
    }
}
