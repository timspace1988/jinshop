<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UsersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Users';
    
    /**
     * Make a grid builder.(These are columns being displayed)
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User);
        
        $grid->column('id', __('ID'));//'id' is User's id attribute, 'ID' is the column name
        //$grid->id('ID');//same with above
        $grid->column('name', __('Name'));
        $grid->column('email', __('Email'));
        $grid->column('email_verified_at', __('Email verified'))->display(function($value){
            return $value ? 'Yes' : 'No';
        });
        //$grid->column('password', __('Password'));
        //$grid->column('remember_token', __('Remember token'));
        $grid->column('created_at', __('Registered at'));
        //$grid->column('updated_at', __('Updated at'));

        //Do not show create button on display page because we do not create users in admin panel
        $grid->disableCreateButton();

        //Do not show "Actions" in each line
        $grid->disableActions();

        $grid->tools(function($tools){
            //Disable batch delete button
            $tools->batch(function($batch){
                $batch->disableDelete();
            });
        });
        return $grid;
    }

    /**
     * Make a show builder.(These are columns appearing on detailed page)
     * (We do not have many columns, and they can all be displayed in "grid", so we can delete this function)
     *
     * @param mixed $id
     * @return Show
     */
    // protected function detail($id)
    // {
    //     $show = new Show(User::findOrFail($id));

    //     $show->field('id', __('Id'));
    //     $show->field('name', __('Name'));
    //     $show->field('email', __('Email'));
    //     $show->field('email_verified_at', __('Email verified at'));
    //     $show->field('password', __('Password'));
    //     $show->field('remember_token', __('Remember token'));
    //     $show->field('created_at', __('Created at'));
    //     $show->field('updated_at', __('Updated at'));

    //     return $show;
    // }

    /**
     * Make a form builder.(This function is used to create and edit users, we don not do it in admin panel, so, we can delete ii)
     *
     * @return Form
     */
    // protected function form()
    // {
    //     $form = new Form(new User());

    //     $form->text('name', __('Name'));
    //     $form->email('email', __('Email'));
    //     $form->datetime('email_verified_at', __('Email verified at'))->default(date('Y-m-d H:i:s'));
    //     $form->password('password', __('Password'));
    //     $form->text('remember_token', __('Remember token'));

    //     return $form;
    // }
}
