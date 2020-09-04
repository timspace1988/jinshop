<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\OrderItem;
use App\Models\Product;
use Faker\Generator as Faker;

$factory->define(OrderItem::class, function (Faker $faker) {
    //randomly select a product from database
    $product = Product::query()->where('on_sale', true)->inRandomOrder()->first();
    //randomly choose one from this product's sku
    $sku = $product->skus()->inRandomOrder()->first();

    return [
        'amount' => random_int(1, 5), //random num between 1 and 5 for user had bought
        'price' => $sku->price,
        'rating' => null,//set null here, we will have a check on order in OrdersSeeder file, if that order got reviewed, we will update this field
        'review' => null,//same with above
        'reviewed_at' => null,//same with above
        'product_id' => $product->id,
        'product_sku_id' => $sku->id,
    ];
});
