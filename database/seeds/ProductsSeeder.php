<?php

use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Create 30 products
        $products = factory(Product::class, 30)->create();
        foreach($products as $product){
            //create 3 skus for current product, and set these skus' product_id as the current product's id
            $skus = factory(ProductSku::class, 3)->create(['product_id' => $product->id]);
            //and update current product's price with the lowest price among skus
            $product->update(['price' => $skus->min('price')]);//min() will find the min value with a given key from a collection
        }
    }
}
