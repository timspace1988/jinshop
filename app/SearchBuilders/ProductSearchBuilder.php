<?php
namespace App\SearchBuilders;

use App\Models\Category;

class ProductSearchBuilder
{
    //initilise the search params
    protected $params = [
        'index' => 'products',
        'type' => '_doc',
        'body' => [
            'query' => [
                'bool' => [
                    'filter' => [],
                    'must' => [],
                ],
            ],
        ],
    ];

    //add paginate to search
    public function paginate($size, $page){
        $this->params['body']['from'] = ($page - 1) * $size;
        $this->params['body']['size'] = $size;
        return $this;
    }

    //search against for sale 
    public function onSale(){
        $this->params['body']['query']['bool']['filter'][] = ['term' => ['on_sale' => true]];

        return $this;
    }

    //search against category
    public function category(Category $category){
        if($category->is_directory){
            $this->params['body']['query']['bool']['filter'][] = [
                'prefix' => ['category_path' => $category->path . $category->id . '-'],
            ];
        }else{
            $this->params['body']['query']['bool']['filter'][] = ['term' => ['category_id' => $category->id]];
        }
        
        return $this;
    }

    //search against keywords
    public function keywords($keywords){
        //if $keywords is not a array, turn it to a array
        $keywords = is_array($keywords) ? $keywords : [$keywords];
        foreach ($keywords as $keyword){
            $this->params['body']['query']['bool']['must'][] = [
                'multi_match' => [
                    'query' => $keyword,
                    'fields' => [
                        'title^3',
                        'long_title^2',
                        'category^2',
                        'description',
                        'skus_title',
                        'skus_description',
                        'properties_value',
                    ],
                ],
            ];
        }

        return $this;
    }

    //properties aggregation
    public function aggregateProperties(){
        $this->params['body']['aggs'] = [
            'properties' => [
                'nested' => [
                    'path' => 'properties',
                ],
                'aggs' => [
                    'properties' => [
                        'terms' => [
                            'field' => 'properties.name',
                        ],
                        'aggs' => [
                            'value' => [
                                'terms' => [
                                    'field' => 'properties.value',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $this;
    }

    //search against product property
    public function propertyFilter($name, $value){
        $this->params['body']['query']['bool']['filter'][] = [
            'nested' => [
                'path' => 'properties',
                'query' => [
                    ['term' => ['properties.search_value' => $name . ':' . $value]],
                ],
            ],
        ];

        return $this;
    }

    //sort result with orders
    public function orderBy($field, $direction){
        if(!isset($this->params['body']['sort'])){
            $this->params['body']['sort'] = [];
        }
        $this->params['body']['sort'][] = [$field => $direction];

        return $this;
    }

    //return the search params we built
    public function getParams(){
        return $this->params;
    }
}