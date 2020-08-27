<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    // public function authorize()
    // {
    //     return false;
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'reviews' => ['required', 'array'],
            'reviews.*.id' => ['required', Rule::exists('order_items', 'id')->where('order_id', $this->route('order')->id)],
            //Check if the order item being reviewed by customer (customer submit the order-item-id via the form)  belongs to the current route's order object
            /*
            Rule::exits('order_items')->where()... without second param 'id' will also do the job, because it searches * defaultly against id field in order_items table for the 'order item' being reviewed
            then ->where() will check if order_id of the 'order item' we just found matches the id of this route's order object 
            $this->route('order) gets current route's order object
            */

            'reviews.*.rating' => ['required', 'integer', 'between:1,5'],
            'reviews.*.review' => ['required'],
        ];
    }

    public function attributes()
    {
        return [
            'reviews.*.rating' => 'rate',
            'reviews.*.review' => 'review',
        ];
    }
}
