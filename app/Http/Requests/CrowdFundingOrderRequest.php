<?php

namespace App\Http\Requests;

use App\Models\CrowdfundingProduct;
use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrowdFundingOrderRequest extends FormRequest
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
                        return $fail('The product does not exist.');
                    }
                    //the interface for placing order of crowdfunding product only accept crowdfunding products
                    if($sku->product->type !== Product::TYPE_CROWDFUNDING) {
                        return $fail('This product does not support crowdfunding.');
                    }
                    if(!$sku->product->on_sale){
                        return $fail('This product is not for sale.');
                    }
                    //if the crowdfunding product is not in STATUS_FUNDING state, you cannot place the order
                    if($sku->product->crowdfunding->status !== CrowdfundingProduct::STATUS_FUNDING){
                        return $fail('The crowdfunding is finished.');
                    }
                    if($sku->stock === 0){
                        return $fail('This product is sold out.');
                    }
                    if($this->input('amount') > 0 && $sku->stock < $this->input('amount')){
                        return $fail('This product does not have enough stock.');
                    }
                },
            ],
            'amount' => ['required', 'integer', 'min:1'],
            'address_id' =>[
                'required',
                Rule::exists('user_addresses', 'id')->where('user_id', $this->user()->id),
            ],
        ];
    }
}
