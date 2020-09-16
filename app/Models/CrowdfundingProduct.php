<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\DefaultDatetimeFormat;

class CrowdfundingProduct extends Model
{
    use DefaultDatetimeFormat;
    
    //3 status of crowdfunding
    const STATUS_FUNDING = 'funding';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAIL = 'fail';

    public static $statusMap = [
        self::STATUS_FUNDING => 'In crowdfunding',
        self::STATUS_SUCCESS => 'Crowdfunding is successful',
        self::STATUS_FAIL => 'Crowdfunding failed',
    ];

    protected $fillable = ['total_amount', 'target_amount', 'user_count', 'status', 'end_at'];

    protected $dates = ['end_at'];//'end_at' will be converted to Carbon type

    public $timestamps = false;//don't create 'created_at' and 'updated_at' fields

    public function product(){
        return $this->belongsTo(Product::class);
    }

    //create a simuliated percent attribute
    public function getPercentAttribute(){
        //current total amount divided by target amount
        $value = $this->attributes['total_amount'] / $this->attributes['target_amount'];//$this->attributes['xxx'] is same with $this->xxx in this project, check the benifit of using ->attributes[] on google

        return floatval(number_format($value * 100, 2, '.', ''));
    }
}
