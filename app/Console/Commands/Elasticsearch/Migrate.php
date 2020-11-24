<?php

namespace App\Console\Commands\Elasticsearch;

use Illuminate\Console\Command;

class Migrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elasticsearch index update migration';

    protected $es;

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
        $this->es = app('es');

        //create an empety index array
        $indices = [Indices\ProjectIndex::class];//namespace, Migrate and ProjectIndex are all under 'App\Console\Commands\Elasticsearch', so here just use Indices\ProjectIndex
        
        //traverse on $indices
        foreach($indices as $indexClass){
            //get current index's aliasname
            $aliasName = $indexClass::getAliasName();
            $this->info('Processing the index ' . $aliasName);

            //before we update each index in elasticsearch, we need to ensure it exists, if not, create it
            //check if Elasticsearch has current index, which can be done by checking if this $aliasName exists in indices(the indices in elasticsearch, not the one we created)
            if(!$this->es->indices()->exists(['index' => $aliasName])){
                $this->info('The index is not existed, prepare for creation');
                $this->createIndex($aliasName, $indexClass);
                $this->info('The index is created successfully, prepare for initialisation');
                $indexClass::rebuild($aliasName);
                $this->info('Opearation successful');
                continue;
            }

            //update this index
            try{
                $this->info('The index exists, prepare for updating');
                $this->updateIndex($aliasName, $indexClass);
            }catch(\Exception $e){
                $this->warn('Updating failed, create new index');
                $this->reCreateIndex($aliasName, $indexClass);
            }
            $this->info('Operation successful on '. $aliasName);
        }
    }

    //create a ne index
    protected function createIndex($aliasName, $indexClass){
        //create new index by calling ->indices()->create()
        $this->es->indices()->create([
            //the surfix for first version of this index is _0, note: index name is aliasName + _num
            'index' => $aliasName . '_0',
            'body' => [
                //get index settings 
                'settings' => $indexClass::getSettings(),
                'mappings' => [
                    'properties' => $indexClass::getProperties(),
                ],
                'aliases' => [
                    //create and set new alias name
                    $aliasName => new \stdClass,
                    //we use new \stdClass here beacause elasticsearch only accept an empty object
                    //if we set $aliasName => [], it will be converted to empty array when json encoding
                ],
            ],
        ]);
    }

    //update existing index
    protected function updateIndex($aliasName, $indexClass){
        //temporarily switch off the index
        $this->es->indices()->close(['index' => $aliasName]);

        //update index settings
        $this->es->indices()->putSettings([
            'index' => $aliasName,
            'body' => $indexClass::getSettings(),
        ]);

        //updating the fields of this index
        $this->es->indices()->putMapping([
            'index' => $aliasName,
            'body' => [
                'properties' => $indexClass::getProperties(),
            ],
        ]);

        //finish updating, switch on the index
        $this->es->indices()->open(['index' => $aliasName]); 
    }

    //recreate the index
    protected function recreateIndex($aliasName, $indexClass){
        //retrieve index info, in the result, 'key' is index name, 'value' is index alias name
        $indexInfo = $this->es->indices()->getAliases(['index' => $aliasName]);

        //get the keys, the first one is what we want 
        $indexName = array_keys($indexInfo)[0];

        //check if the index name ends with '_num' surfix using regular expression
        if(!preg_match('~_(\d+)$~', $indexName, $m)){
            $msg = 'Index name is not in correct format: ' . $indexName;
            $this->error($msg);
            throw new \Exception($msg);
        }

        //if the index name is in correct format, we use its surfix to create the new version index name (old surfix + 1)
        $newIndexName = $aliasName . '_' . ($m[1] + 1);
        $this->info('Creating new index ' . $newIndexName);
        $this->es->indices()->create([
            'index' => $newIndexName,
            'body' => [
                'settings' => $indexClass::getSettings(),
                'mappings' => [
                    'properties' => $indexClass::getProperties(),
                ],
            ],
        ]);

        $this->info('Creating successful, prepare for rebuilding data');
        $indexClass::rebuild($newIndexName);
        $this->info('Rebuilding data successful, link alias name to new index');
        $this->es->indices()->putAlias(['index' => $newIndexName, 'name' => $aliasName]);
        $this->info('Linking alias name successful, delete old index');
        $this->es->indices()->delete(['index' => $indexName]);
        $this->info('Old index deleted');
    }
}
