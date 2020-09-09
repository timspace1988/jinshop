<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProductsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Product';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product());

        //with will load the categories data from database, this will decrease the number of sqls
        $grid->model()->with(['category']);

        $grid->column('id', __('Id'));
        $grid->column('title', __('Product name'));

        //laravel-admin supports get relationship model's attribute using '.'
        $grid->column('category.name', 'Category'); 

        // $grid->column('description', __('Description'));
        // $grid->column('image', __('Image'));
        $grid->column('on_sale', __('For sale'))->display(function ($value){
            return $value ? "Yes" : "No";
        });
        $grid->column('price', __('Price'));
        $grid->column('rating', __('Rating'));
        $grid->column('sold_count', __('Sold count'));
        $grid->column('review_count', __('Review count'));
        
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));

        $grid->actions(function($actions){
            $actions->disableView();
            $actions->disableDelete();
        });

        $grid->tools(function($tools){
            $tools->batch(function ($batch){
                $batch->disableDelete();
            });
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    // protected function detail($id)
    // {
    //     $show = new Show(Product::findOrFail($id));

    //     $show->field('id', __('Id'));
    //     $show->field('title', __('Title'));
    //     $show->field('description', __('Description'));
    //     $show->field('image', __('Image'));
    //     $show->field('on_sale', __('On sale'));
    //     $show->field('rating', __('Rating'));
    //     $show->field('sold_count', __('Sold count'));
    //     $show->field('review_count', __('Review count'));
    //     $show->field('price', __('Price'));
    //     $show->field('created_at', __('Created at'));
    //     $show->field('updated_at', __('Updated at'));

    //     return $show;
    // }

    /**
     * Make a form builder.(add new/edit product)
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Product());

        $form->text('title', __('Product name'))->rules('required');

        //add a toggle search selector for categories, similar to the selector in CategoriesController
        $form->select('category_id')->options(function($id){
            //the difference to selector in CategoriesController is:
            //if the category field here already has data currently(e.g. when we edit an existing product), the current category info should be defaultly displayed, that is why we should have ->options() here, while in CategoriesController, parent field is not allowed editing aftet being created
            //another differentce is: we can only select a catetory which is not a directory for product, while in CategoriesControler, we select the parent, that means is_directory must be true
            //so we adjust the 'admin/api/categories' and apiIndex() in CategoriesController, to do different filtering depending on is_directory's value
            $category = Category::find($id);
            if($category){
                return [$category->id => $category->full_name];//the data output to selector shold always be [id => value] format
            }
        })->ajax('/admin/api/categories?is_directory=0');

        //$form->textarea('description', __('Description'));
        $form->image('image', __('Cover photo'))->rules('required|image');
        $form->quill('description', __('Product description'))->rules('required');
        //$form->switch('on_sale', __('On sale'))->default(1);
        $form->radio('on_sale', __('For sale'))->options(['1' => 'Yes', '0' => 'No'])->default('0');
        
        //Add hasMany relationshio model
        $form->hasMany('skus', 'SKU list', function (Form\NestedForm $form){//the first attribute must be same with the relationship function name in Product model ('skus')
            $form->text('title', 'SKU name')->rules('required');
            $form->text('description', 'SKU description')->rules('required');
            $form->text('price', 'Price')->rules('required|numeric|min:0.01');
            $form->text('stock', 'Stock')->rules('required|integer|min:0');
        });
        // $form->decimal('rating', __('Rating'))->default(5.00);
        // $form->number('sold_count', __('Sold count'));
        // $form->number('review_count', __('Review count'));
        // $form->decimal('price', __('Price'));

        //When clicking on save button, activate a callback to set the product's price with the lowest price among all its skus
        $form->saving(function (Form $form){
            $form->model()->price = collect($form->input('skus'))->where(Form::REMOVE_FLAG_NAME, 0)->min('price') ?: 0; 
            //collect() is a helper function in laravel which helps create a collection and let us use min() function to get minmum value
            //where(Form::REMOVE_FLAG_NAME, 0) when we delete a sku and click save, the collection still includes this sku for min price calculation, but form sets a flag("_remove"1" => "1") to it
            //for all other skus still there, they have "_remove" => 0, and we need to use these ones to do the calculation  
        });

        return $form;
    }
}
