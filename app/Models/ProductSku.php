<?php

namespace App\Models;

use App\Exceptions\InternalException;
use Illuminate\Database\Eloquent\Model;

class ProductSku extends Model
{
    protected $fillable = [
        'title', 'description', 'price', 'stock'
    ];

    //relationship with product
    public function product(){
        return $this->belongsTo(Product::class);
    }

    //decrease the stock, this will be called when an order is placed
    public function decreaseStock($amount){
        if($amount < 0){
            throw new InternalException('The amount to decrease cannot be less than 0');
        }

        //use decrement() to decrease some value on a particular field, and decrement() will return the num of affected lines, if non line is affected, it means decrease operation failed 
        return $this->where('id', $this->id)->where('stock', '>=', $amount)->decrement('stock', $amount);
        //above codes will ensure the stock not be negative after decrease
    }

    //add stock
    public function addStock($amount){
        if($amount < 0){
            throw new InternalException('The amount to add cannot be less than 0');
        }

        $this->increment('stock', $amount);
    }
}
