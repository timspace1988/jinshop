<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallmentRate extends Model
{
    //disable the timestamps
    public $timestamps = false;

    protected $fillable = ['installment_fee_rate', 'min_installment_amount', 'installment_fine_rate'];

    protected $casts = [
        'installment_fee_rate' => 'json',//automatically convert the value of 'installment_fee_rate' to json type when we access it on your model(stored as text type)
    ];

    

    //the model's default value for attributes
    // protected $attributes = [
    //     //installment_phases : fee_rate
    //     // 'installment_fee_rate' => [
    //     //     3 => 1.5, 
    //     //     6 => 2,
    //     //     12 => 2.5,
    //     // ],
    //     'installment_fee_rate' => "{3:1.5, 6:2, 12:2.5}",
    //     'min_installment_amount' => 300,
    //     'installment_fine_rate' => 0.05,
    // ];

    protected static function initialiseRates(){
        self::truncate();//MyModel::truncate() will delete all rows in its table
        self::create([
            //installment_phases : fee_rate
            'installment_fee_rate' => [
                3 => 1.5, 
                6 => 2,
                12 => 2.5,
            ],
            //'installment_fee_rate' => "{'3':1.5, '6':2, '12':2.5}",
            'min_installment_amount' => 300,
            'installment_fine_rate' => 0.05,
        ]);
    }

    protected static function getMinInstallmentAmount(){
        return self::query()->first()->min_installment_amount;
    }

    protected static function getInstallmentFineRate(){
        return self::query()->first()->installment_fine_rate;
    }

    protected static function getInstallmentFeeRate(){
        return self::query()->first()->installment_fee_rate;
    }
}
