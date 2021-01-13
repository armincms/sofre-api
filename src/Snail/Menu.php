<?php

namespace Armincms\SofreApi\Snail;

use Armincms\Snail\Http\Requests\SnailRequest;
use Illuminate\Http\Request;
use Armincms\Snail\Properties\{ID, Text, Number, Map, Collection, BelongsTo};  

class Menu extends Schema
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \Armincms\Sofre\Models\Menu::class;  

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = [
        'food.group', 'restaurant.discounts'
    ];

    /**
     * Get the properties displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function properties(Request $request)
    {
        return [
            ID::make(),

            Text::make('Name', function() {
                return $this->food->name;
            }), 

            Number::make('Rating', function() {
                return 1.3;
            }), 

            Map::make('Material', function() {
                    return collect($this->food->material)->map(function($value, $name) {
                        return compact('name', 'value');
                    })->values();
                })
                ->using(function($attribute) {
                    return Collection::make($attribute)->properties(function() {
                        return [
                            Text::make('Value'),

                            Text::make('Name'),
                        ];
                    });
                }),

            Number::make('Old Price', 'price'),

            Number::make('Price', function() {
                if($this->price > 0) {
                    return $this->restaurant->discounts->filter->canApplyOn($this->food)->reduce(function($discount, $price) {
                        return $discount->applyDiscount($price);
                    }, $this->price); 
                }

                return 0;
            }), 

            Number::make('Discount', function() {
                if($this->price > 0) {
                    $amount = $this->restaurant->discounts->filter->canApplyOn($this->food)->reduce(function($discount, $price) {
                        return $discount->applyDiscount($price);
                    }, $this->price); 

                    return intval($this->price - $amount) / $this->price * 100; 
                }

                return 0;
            }), 

            // Number::make('Discount', function() {
            //     return $this->restaurant->discounts->each->applyDiscount($this->price) / $this->price * 100;
            // }),  

            Collection::make('Image', function($resource) {
                    return $resource->food->getConversions($resource->food->getFirstMedia('image'), [
                        'food-thumbnail', 'food-medium'
                    ]);
                })
                ->properties(function() {
                    return [
                        Text::make('Thumbnail', 'food-thumbnail')->nullable(true, ['']),
                        
                        Text::make('Noobar', 'food-medium')->nullable(true, ['']), 
                    ];
                }),
                
            BelongsTo::make('Restaurant', 'restaurant', Restaurant::class), 
        ];
    }  
}