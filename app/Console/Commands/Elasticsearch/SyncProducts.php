<?php

namespace App\Console\Commands\Elasticsearch;

use App\Models\Product;
use Illuminate\Console\Command;

class SyncProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:sync-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise products data to Elasticsearch';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //firstly we get a Elasticsearch object
        $es = app('es');

        Product::query()->with(['skus', 'properties'])//preload skus and properties
                        ->chunkById(100, function($products) use($es){//chunkById(100, function()) will retrieve 100 products each time to avoid overloaed
                            $this->info(sprintf('Synchronising the products data with ID range from %s to %s', $products->first()->id, $products->last()->id));

                            //Initialise the request
                            $req = ['body' => []];

                            //travers on products
                            foreach($products as $product){
                                //convert product model instance to Elasticsearch document array
                                $data = $product->toESArray();

                                //the following array structure is weird and different from the array in index() method 
                                //e.g. app('es')->index(['id' => $arr['id'], 'index' => 'products', 'body' => $arr]);
                                //This is because we will use mass creation method $es->bulk($req), the structure must follows the codes below
                                
                                $req['body'][] = [
                                    'index' => [
                                        '_index' => 'products',
                                        '_id' => $data['id'],
                                    ],
                                ];
                                $req['body'][] = $data;
                            }
                            //after the above iteration, the $req's structure will be like,:
                            
                            /*
                                "body" => [
                                            [
                                                "index" => [
                                                    "_index" => "products",
                                                    "_id" => 1,
                                                ],
                                            ],
                                            [
                                                "$data for product 1"
                                            ],
                                            [
                                                "index" => [
                                                    "_index" => "products",
                                                    "_id" => 2,
                                                ],
                                            ],
                                            [
                                                "$data for product 2"
                                            ],
                                         ],
                            */
                            //explaination for body
                            //line 1: operation description 
                            //line 2: data
                            //line 3: operation description
                            //line 4: data
                            //...

                            try {
                                //use bulk() for mass creations of Elasticsearch documents(access to Elasticsearch service api for once and do multiple creations)
                                //for single creation, check SyncOneProductToES.php under Jobs directory
                                $es->bulk($req);

                            } catch (\Exception $e) {
                                $this->error($e->getMessage());
                            }

                        });

                        
        $this->info('Synchronisation is done.');
    }
}
