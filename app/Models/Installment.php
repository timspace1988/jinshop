<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_REPAYING = 'repaying';
    const STATUS_FINISHED = 'finished';

    public static $statusMap = [
        self::STATUS_PENDING => 'pending',
        self::STATUS_REPAYING => 'paying',
        self::STATUS_FINISHED => 'finished',
    ];

    protected $fillable = ['no', 'total_amount', 'count', 'fee_rate', 'fine_rate', 'status'];

    protected static function boot(){
        parent::boot();
        //listening on creating event, trigered before saving to database
        static::creating(function($model){
            //if 'no' field is null, generate it
            if(!$model->no){
                $model->no = static::findAvailabelNo();
                //if we failed to generate a 'no', terminate the creating of installment model
                if(!$model->no){
                    return false;
                }
            }
        });
    }

    //relationship with User
    public function user(){
        return $this->belongsTo(User::class);
    }

    //relattionship with Order
    public function order(){
        return $this->belongsTo(Order::class);
    }

    //relationship with InstallmentItem
    public function items(){
        return $this->hasMany(InstallmentItem::class);
    }

    //Generte installment 'no'
    public static function findAvailableNo(){
        $prefix = date('YmdHis');
        for($i = 0; $i < 10; $i++){
            //randomly generate a 6 digit number and attach it to the end of prefix
            $no = $prefix . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            //check if the generated no is already existing, if yes, continue the loop, if no, return the no and end the loop
            if(!static::query()->where('no', $no)->exists()){
                return $no;
            }
        }

        //after a 10-times loop, if we still cannot find a unique no, write down the logs and return false
        \Log::warning('Failed to find a unique installment no');
        return false;         
    }  
}
