<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;

abstract class CommonProductsController extends AdminController
{
    //implements this method to return a product you need in you controller class
    abstract public function getProductType();

    //implements this method to in your controller class to customize your grid for display
    abstract protected function customGrid(Grid $grid);

    //implements this method in your controller class to customize your form for create and edit (add extra fields that not in our common grid/form)
    abstract protected function customForm(Form $form);
    protected function grid(){
        $grid = new Grid(new Product());

        //get the product type we need
        $grid->model()->where('type', $this->getProductType())->orderBy('id', 'desc');

        //call following function to customize our grid
        $this->customGrid($grid);

        $grid->actions(function($actions){
            $actions->disableView();
            $actions->disableDelete();
        });
        $grid->tools(function($tools){
            $tools->batch(function($batch){
                $batch->disableDelete();
            });
        });

        return $grid;
    }

    protected function form(){
        $form = new Form(new Product());

        $form->hidden('type')->value($this->getProductType());
        $form->text('title', 'Product name')->rules('required');
        $form->text('long_title', 'Product long title')->rules('required');
        $form->select('category_id', 'Category')->options(function($id){
            $category = Category::find($id);
            if($category){
                return [$category->id => $category->full_name];
            }
        })->ajax('/admin/api/categories?is_directory=0');
        $form->image('image', 'Cover photo')->rules('required|image');
        $form->quill('description', 'Product description')->rules('required');
        $form->radio('on_sale', 'For sale')->options(['1' => 'Yes', '0' => 'No'])->default('0');

        //customize your form
        $this->customForm($form);
        
        //subform of creating a sku
        $form->hasMany('skus', 'Product SKU', function(Form\NestedForm $form){
            $form->text('title', 'SKU name')->rules('required');
            $form->text('description', 'SKU description')->rules('required');
            $form->text('price', 'Price')->rules('required|numeric|min:0.01');
            $form->text('stock', 'Stock')->rules('required|integer|min:0');
        });

        //subform of creating a product property
        $form->hasMany('properties', 'Product property', function(Form\NestedForm $form){
            $form->text('name', 'Property name')->rules('required');
            $form->text('value', 'Property value')->rules('required');
        });

        $form->saving(function(Form $form){
            $form->model()->price = collect($form->input('skus'))->where(Form::REMOVE_FLAG_NAME, 0)->min('price') ?: 0; 
        });

        return $form;
    }
}