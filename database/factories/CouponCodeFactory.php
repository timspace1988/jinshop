<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CouponCode;
use Faker\Generator as Faker;

$factory->define(CouponCode::class, function (Faker $faker) {
    //at first, we randomly choose a coupon type
    $type = $faker->randomElement(array_keys(CouponCode::$typeMap));

    //generate the random discount based on the type we choose
    $value = $type === CouponCode::TYPE_FIXED ? random_int(1, 200) : random_int(1, 50);
    
    //in addition, if we choose fixed type coupon, the minimum requirement on amount must be at list 0.01 higher than coupon value
    if($type === CouponCode::TYPE_FIXED){
        $minAmount = $value + 0.01;
    }else{
        //if the type is percentage, we make it without any amount requirement in a chance of half and half
        if(random_int(0, 100) < 50){
            $minAmount = 0;
        }else{
            $minAmount = random_int(100, 1000);
        }
    }

    return [
        'name' => join(' ', $faker->words),// generate wors and join them with ' ' as coupon name
        'code' => CouponCode::findAvaiableCode(),
        'type' => $type,
        'value' => $value,
        'total' => 1000,
        'used' => 0,
        'min_amount' =>$minAmount,
        'not_before' => null,
        'not_after' => null,
        'enabled' =>true,
    ];
});
