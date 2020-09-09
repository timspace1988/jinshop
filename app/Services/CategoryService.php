<?php
namespace App\Services;

use App\Models\Category;


class CategoryService
{
    //get categories tree
    //later on, when cursor moved onto a particular (parent) catetory. we make it automatically display all its children categories(when continue moving onto a child, displaying the grand children)
    //$parentId stands for a particular parent category, when it was null, it  gets all root categories(because root category has no parent )
    //$allCategories contains all categories records in database, if it was null, it means, we need to firstly retrieve all records from database
    public function getCategoryTree($parentId = null, $allCategories = null){
        //firstly check if $allCategories is null
        if(is_null($allCategories)){
            //if null, get all categories from database
            $allCategories = Category::all();
        }

        
        //output the tree (in array structure)
        return $allCategories->where('parent_id', $parentId)//find all categories whose parent_id is $parentId from $allCategories
                             //traverse on entries in $allCategories, each entry will be assigned to $category during each iteration 
                             ->map(function(Category $category) use ($allCategories){
                                 //store category info in $data
                                 $data = ['id' => $category->id, 'name' => $category->name];

                                 //if this category is not a directory (means has no child), we can move on to next interaton(entry) by return $data
                                 if(!$category->is_directory){
                                     return $data;
                                 }
                                 //otherwise, we need to build up the categories tree under this category and put it into 'children' then push 'children in $data ($data will have 3 entry: id, name, children)
                                 $data['children'] = $this->getCategoryTree($category->id, $allCategories);
                                 return $data;
                             });
    }
}