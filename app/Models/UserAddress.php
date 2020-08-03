<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = [
        'state',
        'city',
        'suburb',
        'address',
        'postcode',
        'contact_name',
        'contact_phone',
        'last_used_at',
    ];

    //this makes 'last_used_at' a "date" type data. So, $anUserAddress->last_used_at will return a carbon object
    protected $dates = ['last_used_at'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    
    //getXxxYyyAttribute() will create an xxx_yyy attribute for current class. e.g. here you can use $someAddress->full_address to get the full address
    public function getFullAddressAttribute(){
        return "{$this->address}, {$this->suburb}, {$this->state} ";
    }
}
