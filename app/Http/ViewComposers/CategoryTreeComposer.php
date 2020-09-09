<?php
namespace App\Http\ViewComposers;

use App\Services\CategoryService;
use Illuminate\View\View;

class CategoryTreeComposer
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    //later on we will set(register) a group of templates in boot() method of AppServiceProvider.php, which will let laravel call the following compose() method when those templates are rendered
    //check  \View::composer() in boot method of AppServiceProvider.php
    public function compose(View $view){
        //use with() method to pass category varible to front end template(page)
        $view->with('categoryTree', $this->categoryService->getCategoryTree());
    }
}