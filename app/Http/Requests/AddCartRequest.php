<?php

namespace App\Http\Requests;

use App\Models\ProductSku;
use Illuminate\Foundation\Http\FormRequest;

class AddCartRequest extends FormRequest
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
            'sku_id' => [
                'required',
                function($attribute, $value, $fail){
                    if(!$sku = ProductSku::find($value)){
                        return $fail('This product no longer exists.');
                    }
                    if(!$sku->product->on_sale){
                        return $fail('This product is not for sale.');
                    }
                    if($sku->stock === 0){
                        return $fail('This product is sold out.');
                    }
                    if($this->input('amount') > 0 && $sku->stock < $this->input('amount')){
                        return $fail('This product is not enough on stock');
                    }
                }
            ],
            'amount' => ['required', 'integer', 'min:1'],
        ];
    }

    //set alias for attributes to make it meaningful in message
    public function attributes(){
        return ['amount' => 'quantity'];
    }

    //customize error message
    public function messages(){
        return ['sku_id.required'=>'Please select an item.'];
    }
}
