<?php

namespace Armincms\SofreApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Armincms\Sofre\Models\Restaurant;
use Armincms\Sofre\Helper;

class OrderRequest extends FormRequest
{  
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }  

    /**
     * Find restaurnat instance.
     * 
     * @return \Illuminate\DAtabase\Eloqeunt\Model
     */
    public function findRestaurant()
    {
        return once(function() {
            return Restaurant::find($this->restaurant_id);
        });
    }  

    public function validateOrder()
    {
        return $this->validate([
            'sending_method'=> 'required|in:' . implode(',', array_keys(Helper::sendingMethod())),
            'payment_method'=> 'required|in:'. implode(',', array_keys(Helper::paymentMethods())),
            'address'       => 'required|string',
            'restaurant_id' => ['required', function($attribute, $items, $fail) {
                if(is_null($restaurant = $this->findRestaurant())) {
                    return $fail(__('Restaurant not found.'));
                } 
            }],
            'items'  => 'required',
            'items.*'   => function($attribute, $count, $fail) { 
                $menuId = explode('.', $attribute)[1];

                if(is_null($restaurant = $this->findRestaurant())) {
                    return $fail(__('Restaurant not found.'));
                }  

                if(is_null($menu = $restaurant->menus->where('id', $menuId)->first())) {
                    return $fail(__('Item removed.'));
                } 
            }
        ]);  
    } 
}
