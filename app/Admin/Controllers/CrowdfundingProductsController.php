<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\CrowdfundingProduct;
use App\Models\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CrowdfundingProductsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'CrowdfundingProduct';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product());

        // $grid->column('id', __('Id'));
        // $grid->column('type', __('Type'));
        // $grid->column('category_id', __('Category id'));
        // $grid->column('title', __('Title'));
        // $grid->column('description', __('Description'));
        // $grid->column('image', __('Image'));
        // $grid->column('on_sale', __('On sale'));
        // $grid->column('rating', __('Rating'));
        // $grid->column('sold_count', __('Sold count'));
        // $grid->column('review_count', __('Review count'));
        // $grid->column('price', __('Price'));
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));

        //only display products which's type is crowdfunding
        $grid->model()->where('type', Product::TYPE_CROWDFUNDING);
        $grid->id('ID')->sortable();
        $grid->title('Product name');
        $grid->on_sale('For sale')->display(function($value){
            return $value ? 'Yes' : 'No';
        }); 
        $grid->price('Price');
        
        //The followinga are all crowdfunding related  fields, built on relationship (supporting getting attributes using '.')
        $grid->column('crowdfunding.target_amount', 'Target amount');
        $grid->column('crowdfunding.end_at', 'Ending_at');
        $grid->column('crowdfunding.total_amount', 'Target amount');
        $grid->column('crowdfunding.status', 'Status')->display(function($value){
            return CrowdfundingProduct::$statusMap[$value];
        });

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

    // /**
    //  * Make a show builder.
    //  *
    //  * @param mixed $id
    //  * @return Show
    //  */
    // protected function detail($id)
    // {
    //     $show = new Show(Product::findOrFail($id));

    //     $show->field('id', __('Id'));
    //     $show->field('type', __('Type'));
    //     $show->field('category_id', __('Category id'));
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
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Product());

        // $form->text('type', __('Type'))->default('normal');
        // $form->number('category_id', __('Category id'));
        // $form->text('title', __('Title'));
        // $form->textarea('description', __('Description'));
        // $form->image('image', __('Image'));
        // $form->switch('on_sale', __('On sale'))->default(1);
        // $form->decimal('rating', __('Rating'))->default(5.00);
        // $form->number('sold_count', __('Sold count'));
        // $form->number('review_count', __('Review count'));
        // $form->decimal('price', __('Price'));

        //We add a hidden field name 'type', its value is Product::TYPE_CROWDFUNDING
        $form->hidden('type')->value(Product::TYPE_CROWDFUNDING);
        $form->text('title', 'Product name')->rules('required');
        //a category selector (with search function)
        $form->select('category_id', 'Category')->options(function($id){
            $category = Category::find($id);
            if($category){
                return [$category->id => $category->full_name];
            }
        })->ajax('/admin/api/categories?is_directory=0');
        $form->image('image', 'Cover photo')->rules('required|image');
        $form->quill('description', 'Product description')->rules('required');
        $form->radio('on_sale', 'For sale')->options(['1' => 'Yes', '0' => 'No'])->default('0');

        //Followings are crowdfunding related field, built up based on relationship
        $form->text('crowdfunding.target_amount', 'Target amount')->rules('required|numeric|min:0.01');
        $form->datetime('crowdfunding.end_at', 'End_at')->rules('required|date');
        $form->hasMany('skus', 'Product sku', function(Form\NestedForm $form){
            $form->text('title', 'SKU name')->rules('required');
            $form->text('description', 'SKU description')->rules('required');
            $form->text('price', 'Price')->rules('required|numeric|min:0.01');
            $form->text('stock', 'Stock')->rules('required|integer|min:0');
        });
        $form->saving(function(Form $form){
            $form->model()->price = collect($form->input('skus'))->where(Form::REMOVE_FLAG_NAME, 0)->min('price');
        });

        return $form;
    }
}
