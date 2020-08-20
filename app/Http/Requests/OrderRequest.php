<?php

namespace App\Http\Requests;

use App\Models\ProductSku;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
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
            //check if the address id submitted by user matches the record in database and belongsto him
            'address_id' => [
                'required',
                Rule::exists('user_addresses', 'id')->where('user_id', $this->user()->id),
            ],
            'items' => ['required', 'array'],
            'items.*.sku_id' => [
                'required',//check all sub-array's sku_id of items array
                function($attribute, $value, $fail){//$attribute here is the 'items.*.sku_id'
                    if(!$sku = ProductSku::find($value) ){
                        return $fail('This product does not exists.');
                    }
                    if(!$sku->product->on_sale){
                        return $fail($sku->product->title . ' ' . $sku->title . ' is not for sale.');
                    }
                    if(!$sku->stock ===0){
                        return $fail($sku->product->title . ' ' . $sku->title . ' is sold out.');
                    }
                    //get currently validating sku's index in the items arry
                    preg_match('/items\.(\d+)\.sku_id/', $attribute, $m);//e.g. when $attribute = 'items.0.sku_id', $m = ['items.0.sku_id', '0']
                    $index = $m[1];
                    //Use  this index to get the number of this sku submited by customer
                    $amount = $this->input('items')[$index]['amount'];
                    if($amount > 0 && $amount > $sku->stock){
                        return $fail($sku->product->title . ' ' . $sku->title . ' does not have enough stock.');
                    }
                }
            ],
            'items.*.amount' => ['required', 'integer', 'min:1'],
        ];


    }

    public function messages(){
        return [
            'items.required' => 'Please select at least one item to place your order',
            'address_id.required' => 'You have not set any address yet, please add one before continue.',
        ];
    }
}
