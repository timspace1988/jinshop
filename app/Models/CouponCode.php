<?php

namespace App\Models;

use App\Exceptions\CouponCodeUnavailableException;
use Carbon\Carbon;
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

    //Check if a coupon is available, 
    //we will check it in CouponCodesController's show method when user click apply coupon button
    //However before user finally place the order. the coupon could have already been used out or changed condisions, so we need to recheck its availability when placing the order
    public function checkAvailable($orderAmount = null){
        if(!$this->enabled){
            throw new CouponCodeUnavailableException('Coupon code does not exist.');
        }

        if($this->total - $this->used <= 0){
            throw new CouponCodeUnavailableException('Coupon code has used out.');
        }

        if($this->not_before && $this->not_before->gt(Carbon::now())){
            throw new CouponCodeUnavailableException('Coupon code is not available yet.');
        }

        if($this->not_after && $this->not_after->lt(Carbon::now())){
            throw new CouponCodeUnavailableException('Coupon code is expired.');
        }

        //we will pass the orderAmount in this method, if it is null, it will not execute following code
        if(!is_null($orderAmount) && $orderAmount < $this->min_amount){
            throw new CouponCodeUnavailableException('Order amount does not meet minimum requirement.');
        }
    }

    //get the final price after coupon applied
    public function getAdjustedPrice($orderAmount){
        //if the discount is fixed type
        if($this->type === self::TYPE_FIXED){
            //to improve the robustness of system, we need the order's final amount cannot be less than 0.01
            return max(0.01, $orderAmount - $this->value);
        }

        //for percent type
        return number_format($orderAmount * (100 - $this->value)/100, 2, '.', '');
    }

    //change the usage 
    public function changeUsed($increase = true){
        //if passing true, means increasing the used. false means the opposite
        if($increase){
            //similar to check sku stock, we need to ensure the used will not exceed the total before we increase the used
            return $this->where('id', $this->id)->where('used', '<', $this->total)->increment('used');//it will return number of affected line, if <=0, it means failed(coupon is used out)
            //here $this->where('id', $this->id) equals to self::query()->where('id', $this->id), without this condition, it will retrieve all coupons with used < total
            
        }else{
            return $this->decrement('used');
        }
    }

}
