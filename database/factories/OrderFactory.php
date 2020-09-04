<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CouponCode;
use App\Models\Order;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Order::class, function (Faker $faker) {
    //firstly, we randomly find a user
    $user = User::query()->inRandomOrder()->first();
    //Randomly get a address of this user
    $address = $user->addresses()->inRandomOrder()->first();
    //randomly mark the order as refunded with 10% chance
    $refund = rand(0, 10)<1;//random_int(0, 10) < 1 is actually give a 1/11 chance here. Note:rand()doesn't give us a real random integer, it gives us a pseudo one, it's not secure(can be predicted) but fast
    //generate random shipping status
    $ship = $faker->randomElement(array_keys(Order::$shipStatusMap));
    //Coupon
    $coupon = null;
    //give it 30% chance to use a random coupon
    if(random_int(0, 10) < 3){
        //We only use coupons without min-amount requirement to avoid anu unexpected mistake
        $coupon = CouponCode::query()->where('min_amount', 0)->inRandomOrder()->first();
        //do not forget to increase this coupon's usage
        $coupon->changeUsed();
    }
    return [
        'address' => [
            'address' => $address->full_address,
            'zip' => $address->zip,
            'contact_name' => $address->contact_name,
            'contact_phone' => $address->contact_phone,
        ],
        'total_amount' => 0,//we temporarily set it 0, after we generate the order items, we will update it in OrdersSeeder file
        'remark' => $faker->sentence,
        'paid_at' =>$faker->dateTimeBetween('-30 days'),//from last 30 days to now
        'payment_method' => $faker->randomElement(['alipay', 'alipay']),
        'payment_no' => $faker->uuid,
        'refund_status' => $refund ? Order::REFUND_STATUS_SUCCESS : Order::REFUND_STATUS_PENDING,
        'refund_no' => $refund ? Order::getAvailableRefundNo() : null,
        'closed' => false,
        'reviewed' => random_int(0, 10) > 2,
        'ship_status' => $ship,
        'ship_data' => $ship === Order::SHIP_STATUS_PENDING ? null : ['express_company' => $faker->company, 'express_no' => $faker->uuid,],
        'extra' => $refund ? ['refund_reason' => $faker->sentence] : [],
        'user_id' => $user->id,
        'coupon_code_id' => $coupon ? $coupon->id : null,
    ];
});
