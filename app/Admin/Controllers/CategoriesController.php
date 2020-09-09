<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class CategoriesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Product Categories';

    //Edit interface
    //we rewrite the edit function in AdminController, only difference is we pass true to ->form() in last line
    public function edit($id, Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body($this->form(true)->edit($id));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Category());

        $grid->id('ID')->sortable();
        $grid->name('Category name');
        $grid->level('Level');
        $grid->is_directory('Is directory')->display(function($value){
            return $value ? 'Yes' : 'No';
        });
        $grid->path('Category path');
        $grid->actions(function($actions){
            //not display the view button
            $actions->disableView();
        });

        // $grid->column('id', __('Id'));
        // $grid->column('name', __('Name'));
        // $grid->column('parent_id', __('Parent id'));
        // $grid->column('is_directory', __('Is directory'));
        // $grid->column('level', __('Level'));
        // $grid->column('path', __('Path'));
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
    //     $show = new Show(Category::findOrFail($id));

    //     $show->field('id', __('Id'));
    //     $show->field('name', __('Name'));
    //     $show->field('parent_id', __('Parent id'));
    //     $show->field('is_directory', __('Is directory'));
    //     $show->field('level', __('Level'));
    //     $show->field('path', __('Path'));
    //     $show->field('created_at', __('Created at'));
    //     $show->field('updated_at', __('Updated at'));

    //     return $show;
    // }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($isEditing = false)
    {
        $form = new Form(new Category());

        $form->text('name', 'Category name')->rules('required');

        //if user is editing not creating
        if($isEditing){
            //don't allow user to change the value of is_directory and parent field, with(function($value)), $value is the value of "is_directory"
            $form->display('is_directory', 'Is directory')->with(function($value){
                return $value ? 'Yes' : 'No';
            });

            //for relationship field. it supports geting attribute using a dot '.'
            $form->display('parent.name', 'Parent category');

        }else{//if in creating mode
            //a single selector for is_directory field
            $form->radio('is_directory', 'Is directory')->options(['1' => 'Yes', '0' => 'No'])
                                                        ->default('0')
                                                        ->rules('required');

            //a toggle (search) selector for parent category
            $form->select('parent_id', 'Parent category')->ajax('/admin/api/categories');
            //ajax() means laravel-admin will search user's input through the api '/admin/api/categories',  we set it in routes php: $router->get('api/categories', 'CategoriesController@apiIndex')
            //user's input  will be put in 'q' and passed to the api,
            //the api 's logic is defined in apiIndex(Request $request) function
            //user's input can be retrieved using $request->input('q')
        }

        // $form->text('name', 'Category name');
        // $form->number('parent_id', __('Parent id'));
        // $form->switch('is_directory', __('Is directory'));
        // $form->number('level', __('Level'));
        // $form->text('path', __('Path'));

        return $form;
    }

    //We define the toggle selector search logic here
    public function apiIndex(Request $request){
        //retrieve user's search from input 'q'
        $search = $request->input('q');
        $result = Category::query()->where('is_directory', boolval($request->input('is_directory', true)))//we change the line underneath, beacause the is_directory value is passed via uri now (e.g. from ProductsController) the default value is still true
                                   //->where('is_directory', true)//as we are searching for parent category, so we limit the searching pool to the ones having child
                                   ->where('name', 'like', '%'.$search.'%')//search categories for the one macthing the pattern
                                   ->paginate();//and display results in paginate, (we don't want to display all results in a long list)
        
        //$result are bunch of Category objects, but we want to display it in the toggle selector, so we need to adjust it to forms accepted by laravel-admin for display
        $result->setCollection($result->getCollection()->map(function(Category $category){
            //$result is output via paginate, setCollection(some_data) is to replace the paginatee(current page)'s data with some_data, getCollection() is to get the paginate(current page)'s data
            //both methods above cannot be found in documents, need to check the source code
            //$someCollection->map(function(Category $category)) will traverse each entry in the collection and alter the data (for each entry) in function, and then output the altered data
            return ['id' => $category->id, 'text' => $category->full_name];
        }));

        return $result;
    }
}
