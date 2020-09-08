<?php

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'name'     => 'Phone accessories',
                'children' => [
                    ['name' => 'Case'],
                    ['name' => 'Screen protector'],
                    ['name' => 'Memory card'],
                    ['name' => 'Data cable'],
                    ['name' => 'Charger'],
                    [
                        'name'     => 'Earphones',
                        'children' => [
                            ['name' => 'Wired earphones'],
                            ['name' => 'Wireless bluetooth earphones'],
                        ],
                    ],
                ],
            ],
            [
                'name'     => 'Computer accessories',
                'children' => [
                    ['name' => 'Monitor'],
                    ['name' => 'GPU'],
                    ['name' => 'RAM memory'],
                    ['name' => 'CPU'],
                    ['name' => 'Motherboard'],
                    ['name' => 'Hard disk'],
                ],
            ],
            [
                'name'     => 'Computer',
                'children' => [
                    ['name' => 'Laptop'],
                    ['name' => 'Desktop'],
                    ['name' => 'Tablet'],
                    ['name' => 'All-In-One'],
                    ['name' => 'Server'],
                    ['name' => 'Workstation'],
                ],
            ],
            [
                'name'     => 'Phone',
                'children' => [
                    ['name' => 'Smart phone'],
                    ['name' => 'Phone for elderly'],
                    ['name' => 'Walkie talkie'],
                ],
            ],
        ];

        foreach($categories as $data){
             $this->createCategory($data);
        }
    }

    protected function createCategory($data, $parent = null){
        //create a new category object
        $category = new Category(['name' => $data['name']]);
        //if this categories entry has a children entry, it meas the category object just created is a parent category, we need to set is_directory field of this Category instance to true
        $category->is_directory = isset($data['children']);
        //if the $parent param is passed in this function, we need to set it associated with its parent category(by doing this, it will setup the parent_id(foreign key) and relationship) 
        if(!is_null($parent)){
            $category->parent()->associate($parent);
        }
        //save it to database
        $category->save();

        //if this categotry has children entry, and children entry is an array, we need to call this function on each of its children entry, and also pass this category as $parent param to it
        if(isset($data['children']) && is_array($data['children'])){
            //do iteration traverse on each of children entry
            foreach($data['children'] as $child){
                //recursion calling of createCategory function
                $this->createCategory($child, $category); 
            }
        }
    }
}
