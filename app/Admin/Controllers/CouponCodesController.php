<?php

namespace App\Admin\Controllers;

use App\Models\CouponCode;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CouponCodesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\CouponCode';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CouponCode());

        $grid->model()->orderBy('created_at', 'desc');//set defaultly sorted in descending order of 'created_at'
        $grid->id('ID')->sortable();
        $grid->name('Coupon name');
        $grid->code('Coupon code');
        $grid->type('Coupon type')->display(function($value){
            return CouponCode::$typeMap[$value];
        });
        
        // //different type has differernt ways to dispay its value e.g. $20 and 20%
        // $grid->value('Discount')->display(function($value){
        //     return $this->type === CouponCode::TYPE_FIXED ? '$ ' . $value : $value . '%'; 
        // });
        //$grid->min_amount('Minimum amount requirement');

        //here we use a more understandable way to describe the coupon value and its amount requirement instead of above two methods
        $grid->description('Discount');

        // $grid->total('Total');
        // $grid->used('Already used')->display(function($value){
        //     return $value ? 'Yes' : 'No';
        // });

        //here we simulate a 'fake' field usage(not appears in database table) to respresnt the 'total' and the 'used' instead of
        $grid->column('usage', 'Usage')->display(function($value){
            return "{$this->used} / {$this->total}";
        });
        $grid->created_at('Created_at');
        $grid->actions(function($actions){
            $actions->disableView();
        });

        // $grid->column('id', __('Id'));
        // $grid->column('name', __('Name'));
        // $grid->column('code', __('Code'));
        // $grid->column('type', __('Type'));
        // $grid->column('value', __('Value'));
        // $grid->column('total', __('Total'));
        // $grid->column('used', __('Used'));
        // $grid->column('min_amount', __('Min amount'));
        // $grid->column('not_before', __('Not before'));
        // $grid->column('not_after', __('Not after'));
        // $grid->column('enabled', __('Enabled'));
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));

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
    //     $show = new Show(CouponCode::findOrFail($id));

    //     $show->field('id', __('Id'));
    //     $show->field('name', __('Name'));
    //     $show->field('code', __('Code'));
    //     $show->field('type', __('Type'));
    //     $show->field('value', __('Value'));
    //     $show->field('total', __('Total'));
    //     $show->field('used', __('Used'));
    //     $show->field('min_amount', __('Min amount'));
    //     $show->field('not_before', __('Not before'));
    //     $show->field('not_after', __('Not after'));
    //     $show->field('enabled', __('Enabled'));
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
        $form = new Form(new CouponCode());

        $form->text('name', __('Name'));
        $form->text('code', __('Code'));
        $form->text('type', __('Type'));
        $form->decimal('value', __('Value'));
        $form->number('total', __('Total'));
        $form->number('used', __('Used'));
        $form->decimal('min_amount', __('Min amount'));
        $form->datetime('not_before', __('Not before'))->default(date('Y-m-d H:i:s'));
        $form->datetime('not_after', __('Not after'))->default(date('Y-m-d H:i:s'));
        $form->switch('enabled', __('Enabled'));

        return $form;
    }
}
