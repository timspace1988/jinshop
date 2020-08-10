<?php

namespace App\Admin\Controllers;

use App\Models\Jia;
use App\models\User;
use App\Models\UserAddress;
use App\Models\UserTest;
use App\Models\UserUser;
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
        //dd("Hello");
        
        $myUser = new User;
        dd("Hello world");
        try{
            $grid = new Grid($myUser);
        }catch(\Exception $e){
            // $e->getMessage();
            // $e->getFile();
            // $e->getLine();
            dd($e);
        }
        
        //dd("Hello");
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
        //dd("Hello");
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

    // protected function grid()
    // {
    //     $grid = new Grid(new User);

    //     // 创建一个列名为 ID 的列，内容是用户的 id 字段
    //     $grid->id('ID');

    //     // 创建一个列名为 用户名 的列，内容是用户的 name 字段。下面的 email() 和 created_at() 同理
    //     $grid->name('用户名');

    //     $grid->email('邮箱');

    //     $grid->email_verified_at('已验证邮箱')->display(function ($value) {
    //         return $value ? '是' : '否';
    //     });

    //     $grid->created_at('注册时间');

    //     // 不在页面显示 `新建` 按钮，因为我们不需要在后台新建用户
    //     $grid->disableCreateButton();
    //     // 同时在每一行也不显示 `编辑` 按钮
    //     $grid->disableActions();

    //     $grid->tools(function ($tools) {
    //         // 禁用批量删除按钮
    //         $tools->batch(function ($batch) {
    //             $batch->disableDelete();
    //         });
    //     });

    //     return $grid;
    // }

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
