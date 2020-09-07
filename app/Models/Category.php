<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'is_directory', 'level'. 'path'];

    protected $casts = ['is_directory' => 'boolean'];

    //we can register listening and dispach event in model class
    protected static function boot(){
        parent::boot();

        //listening on Categoty model creating event, it will initialise values on path and level fields
        static::creating(function(Category $category){
            //if the one being created is a root catogery
            if(is_null($category->parent_id)){
                //set its level to 0
                $category->level = 0;
                //set path to '-'
                $category->path = '-';
            }else{
                //if it is not a root  category, set its level to its parent's level + 1
                $category->level = $category->parent->level + 1;
                //set its path to its parent's path + parent's id + '-'
                $category->path = $category->parent->path . $category->parent_id . '-';
            }
        });
    }

    //relationships
    public function parent(){
        return $this->belongsTo(Category::class);
    }

    public function children(){
        return $this->hasMany(Category::class,  'parent_id');//someRelationship(p1, p2), p2 is the foreign key in p1, it helps build up correct relationship between same type model 
    }

    public function products(){
        return $this->hasMany(Product::class);
    }

    //create an attribute to get all ancestors' ids
    public function getPathIdsAttribute(){
        return array_filter(explode('-', trim($this->path, '-')));
        //trim($this->path, '-') remove '-' from both beginning and end
        //array_filter() will remove all null entry (empty value) from the array
    }

    //create an attribute to get all sorted parent(ancestor) categories
    public function getAncestorsAttribute(){
        return Category::query()->whereIn('id', $this->path_ids)
                                ->orderBy('level')
                                ->get();
    }

    //create an attribute to get the full name of categories(both ancestors ans current category) and seperate them with '-'
    public function getFullNameAttribute(){
        return $this->ancestors//get all ancestor catogeries
                    ->pluck('name')//retrieve all name filed of these ancestors and put them in an array
                    ->push($this->name)//add current category's name to the end of the array 
                    ->implode('-');//convert the array to string using '-' connecting each entry
    }
}
