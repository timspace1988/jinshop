<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\UserAddress;
use Faker\Generator as Faker;

$factory->define(UserAddress::class, function (Faker $faker) {
    $addresses = [
        ["NSW", "Newtown"],
        ["NSW", "Parramatta"],
        ["ACT", "Latham"],
        ["ACT", "Braddon"],
        ["WA", "Dianella"],
        ["WA", "Morley"]
    ];
    
    //$road_types = ["Road", "Street", "Avenue"];

    $address  = $faker->randomElement($addresses);
    //$road_type = $faker->randomElement($road_types);
 
    return [
        'state' => $address[0],
        'suburb' => $address[1],
        'address' => sprintf("%d %s", $faker->randomNumber(2), $faker->streetName,),
        'postcode' => $faker->postcode,//$faker->randomNumber(4, true),
        'contact_name' => $faker->name,
        'contact_phone' => $faker->phoneNumber,
    ];
});
