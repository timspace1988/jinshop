<?php
namespace App\Console\Commands\Elasticsearch\Indices;

use Artisan;

class ProjectIndex
{
    public static function getAliasName(){
        return 'products';
    }

    public static function getProperties(){
        if(getenv('IS_IN_HEROKU')){
            return [
                'type'          => ['type' => 'keyword'],
                'title'         => ['type' => 'text', 'analyzer' => 'smartcn', 'search_analyzer' => 'smartcn_synonym'],
                'long_title'    => ['type' => 'text', 'analyzer' => 'smartcn', 'search_analyzer' => 'smartcn_synonym'],
                'category_id'   => ['type' => 'integer'],
                'category'      => ['type' => 'keyword'],
                'category_path' => ['type' => 'keyword'],
                'description'   => ['type' => 'text', 'analyzer' => 'smartcn'],
                'price'         => ['type' => 'scaled_float', 'scaling_factor' => 100],
                'on_sale'       => ['type' => 'boolean'],
                'rating'        => ['type' => 'float'],
                'sold_count'    => ['type' => 'integer'],
                'review_count'  => ['type' => 'integer'],
                'skus'          => [
                    'type'       => 'nested',
                    'properties' => [
                        'title'       => [
                            'type'            => 'text',
                            'analyzer'        => 'smartcn',
                            'search_analyzer' => 'smartcn_synonym',
                            'copy_to'         => 'skus_title',
                        ],
                        'description' => [
                            'type'     => 'text',
                            'analyzer' => 'smartcn',
                            'copy_to'  => 'skus_description',
                        ],
                        'price'       => ['type' => 'scaled_float', 'scaling_factor' => 100],
                    ],
                ],
                'properties'    => [
                    'type'       => 'nested',
                    'properties' => [
                        'name'         => ['type' => 'keyword'],
                        'value'        => ['type' => 'keyword', 'copy_to' => 'properties_value'],
                        'search_value' => ['type' => 'keyword'],
                    ],
                ],
            ];
        }else{
            return [
                'type'          => ['type' => 'keyword'],
                'title'         => ['type' => 'text', 'analyzer' => 'ik_smart', 'search_analyzer' => 'ik_smart_synonym'],
                'long_title'    => ['type' => 'text', 'analyzer' => 'ik_smart', 'search_analyzer' => 'ik_smart_synonym'],
                'category_id'   => ['type' => 'integer'],
                'category'      => ['type' => 'keyword'],
                'category_path' => ['type' => 'keyword'],
                'description'   => ['type' => 'text', 'analyzer' => 'ik_smart'],
                'price'         => ['type' => 'scaled_float', 'scaling_factor' => 100],
                'on_sale'       => ['type' => 'boolean'],
                'rating'        => ['type' => 'float'],
                'sold_count'    => ['type' => 'integer'],
                'review_count'  => ['type' => 'integer'],
                'skus'          => [
                    'type'       => 'nested',
                    'properties' => [
                        'title'       => [
                            'type'            => 'text',
                            'analyzer'        => 'ik_smart',
                            'search_analyzer' => 'ik_smart_synonym',
                            'copy_to'         => 'skus_title',
                        ],
                        'description' => [
                            'type'     => 'text',
                            'analyzer' => 'ik_smart',
                            'copy_to'  => 'skus_description',
                        ],
                        'price'       => ['type' => 'scaled_float', 'scaling_factor' => 100],
                    ],
                ],
                'properties'    => [
                    'type'       => 'nested',
                    'properties' => [
                        'name'         => ['type' => 'keyword'],
                        'value'        => ['type' => 'keyword', 'copy_to' => 'properties_value'],
                        'search_value' => ['type' => 'keyword'],
                    ],
                ],
            ];
        }
        
    }

    public static function getSettings(){
        if(getenv('IS_IN_HEROKU')){
            return [
                'analysis' => [
                    'analyzer' => [
                        'smartcn_synonym' => [
                            'type'      => 'custom',
                            'tokenizer' => 'smartcn_tokenizer',
                            'filter'    => ['synonym_filter'],
                        ],
                    ],
                    'filter'   => [
                        'synonym_filter' => [
                            'type'          => 'synonym',
                            'synonyms' => [
                                'i-pod, i pod => ipod',
                                'universe, cosmos',
                                'iPhone, i phone, Iphone, 苹果手机 => iPhone, i phone, Iphone, 苹果手机',
                                '内存, 内存条 => 内存, 内存条',
                            ],
                        ],
                    ],
                ],
            ];
        }else{
            return [
                'analysis' => [
                    'analyzer' => [
                        'ik_smart_synonym' => [
                            'type'      => 'custom',
                            'tokenizer' => 'ik_smart',
                            'filter'    => ['synonym_filter'],
                        ],
                    ],
                    'filter'   => [
                        'synonym_filter' => [
                            'type'          => 'synonym',
                            'synonyms_path' => 'analysis/synonyms.txt',
                        ],
                    ],
                ],
            ];
        }
        
    }

    public static function rebuild($indexName){
        //call an artisan command using 'call'
        //second param in call method could be an array of params
        Artisan::call('es:sync-products', ['--index' => $indexName]);
    }
}