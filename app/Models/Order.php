<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Order extends Model
{
    //use this trait so that we can display date and time in default date time format
    use DefaultDatetimeFormat;

    const REFUND_STATUS_PENDING = 'pending';
    const REFUND_STATUS_APPLIED = 'applied';
    const REFUND_STATUS_PROCESSING = 'processing';
    const REFUND_STATUS_SUCCESS = 'success';
    const REFUND_STATUS_FAILED = 'failed';

    const SHIP_STATUS_PENDING = 'pending';
    const SHIP_STATUS_DELIVERED = 'delivered';
    const SHIP_STATUS_RECEIVED = 'received';

    public static $refundStatusMap = [
        self::REFUND_STATUS_PENDING => 'Pending',
        self::REFUND_STATUS_APPLIED => 'Applied for refund',
        self::REFUND_STATUS_PROCESSING => 'Refund is in processing',
        self::REFUND_STATUS_SUCCESS => 'Refunded',
        self::REFUND_STATUS_FAILED => 'Refund declined',
    ];

    public static $shipStatusMap = [
        self::SHIP_STATUS_PENDING => 'Pending',
        self::SHIP_STATUS_DELIVERED => 'In delivery',
        self::SHIP_STATUS_RECEIVED => 'Received',
    ];

    protected $fillable = [
        'no',
        'address',
        'total_amount',
        'remark',
        'paid_at',
        'payment_method',
        'payment_no',
        'refund_status',
        'refund_no',
        'closed',
        'reviewed',
        'ship_status',
        'ship_data',
        'extra',
    ];

    protected $casts = [
        'closed' => 'boolean',
        'reviewed' => 'boolean',
        'address' => 'json',//Automatically convert it to json type when saving into database
        'ship_data' => 'json',
        'extra' => 'json',
    ];

    protected $dates = [
        'paid_at',
    ];

    protected static function boot(){
        parent::boot();
        //listening on model creating event, 
        static::creating(function($model){
            //this function will be executed before data being written to database
            if(!$model->no){
                //if this model's 'no' is empty, call findAvaiableNo() to generate the order no
                $model->no = static::findAvaiableNo();
                //if generation failed, terminate creating order 
                if(!$model->no){
                    return false;
                }
            }
        });
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function items(){
        return $this->hasMany(OrderItem::class);
    }

    public static function findAvaiableNo(){
        $prefix = date('YmdHis');
        for($i = 0; $i<10; $i++){
            //generate order no by connecting the prefix with a random 6-digit number
            $no = $prefix.str_pad(random_int(0,999999), 6, '0', STR_PAD_LEFT);//str_pad() will convert 126 to 000126
            //call this class's static function query() to check if this order no already exists, if not, return the 'no' just generated
            if(!static::query()->where('no', $no)->exists()){
                return $no;
            }
        }
        /*
        if the generated no is same with some existing order no, it means they have a conflict, two order with same no is not allowed, 
        we cannot use this generated no, so we keep generating for 10 times until we got the un-conflict one,
        however, if we still cannot get a unique no, we give out a warning about failed generating order no
        note: the above code will ensure the randomly generated order no is unique, because the order no is important (usually we can get one within 10 times)
        */
        \Log::warning('Failed to generate a unique order no');
        return false;
    }

    //generate uniqi refund number(we need to use a uniqe number for refund if refund is approved)
    public function getAvailableRefundNo(){
        do{
            //Uuid class can generate a very (big changce) unique string
            $no = Uuid::uuid4()->getHex();
        }while(self::query()->where('refund_no', $no)->exists());//a do-while, if same no exists in orders table, the do {} will continue, 

        return $no;
    }
}
