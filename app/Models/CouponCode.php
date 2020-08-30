<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use function PHPSTORM_META\map;

class CouponCode extends Model
{
    use DefaultDatetimeFormat;

    //define coupon type with const veriable
    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENT = 'percent';

    public static $typeMap = [
        self::TYPE_FIXED => 'Fixed amount',
        self::TYPE_PERCENT => 'Percentage',
    ];

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'total',
        'used',
        'min_amount',
        'not_before',
        'not_after',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    //Make these two fields known as date type
    protected $dates = ['not_before', 'not_after'];

    protected $appends = ['description'];
    //if we call description attribute in laravel controllers, we don't need this code above, because when laravel model object is retrived in laravel controller  it will be convert to a json type
    //and description field is also included in it. e.g. {'description' : 'its description...'}, so we can call $someCoupon->description, directly.
    //However in laravel-admin controlers, the model will firstly be executed a toArrary() before it being converted to json type, and description is not in this array. 
    //In ordet to call ->description, we need to add protected $appends = ['description']; in the model

    //Generate unique coupon code
    public static function findAvaiableCode($length = 16){
        do{
            //get a random string with fixed length and convert it to capitalised 
            $code = strtoupper(Str::random($length));
            //if the generated code  alread appears in database(means we generate duplicate code), continuet loop until we got a unique one
        }while(self::query()->where('code', $code)->exists());

        return $code;
    }

    //we createa a description attribute for CouponCode objcet, which will describe the coupon in a friendly and understandable way
    public function getDescriptionAttribute(){
        $str = '';
        if($this->type === self::TYPE_FIXED){
            $str = '$ ' . str_replace('.00', '', $this->value) . ' off';
        }else{
            $str = str_replace('.00', '', $this->value) . ' % off';
        }
   
        if($this->min_amount > 0){
            return $str . ' on order over $ ' . str_replace('.00', '', $this->min_amount);
        }
        return $str;
    }

}
