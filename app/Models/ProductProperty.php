<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductProperty extends Model
{
    protected $fillable = ['name', 'value'];

    public $timestamps = false;//no created_at and updated_at fields

    //relationship with Product
    public function product(){
        return $this->belongsTo(Product::class);
    }
}
