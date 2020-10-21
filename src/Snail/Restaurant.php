<?php

namespace Armincms\SofreApi\Snail;

use Armincms\Snail\Http\Requests\SnailRequest;
use Illuminate\Http\Request;
use Armincms\Snail\Properties\{
    ID, Text, Boolean, Integer, Number, Map, Collection, BelongsTo, BelongsToMany
};
use Armincms\Sofre\Helper;  

class Restaurant extends Schema
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \Armincms\Sofre\Restaurant::class;  

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = [
        'type', 'areas', 'chain', 'foods'
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

            Text::make('Name'),

            Boolean::make('Online'),

            Number::make('Min Order'), 

            Map::make('Sending Method'), 

            Map::make('Payment Method'),

            Map::make('Menu', 'foods') 
                ->resolveUsing(function($foods) {  
                    return $foods->filter(function($food) {
                        return ! empty(data_get($food->pivot, strtolower(now()->format('l'))));
                    });
                })
                ->using(function($attribute) { 
                    return  Collection::make($attribute)
                                ->properties(function() {
                                    return [
                                        ID::make(),

                                        Text::make('Name'),

                                        Number::make('Price', 'pivot->price'),

                                        Integer::make('Duration', 'pivot->duration'),

                                        Boolean::make('Available', 'pivot->duration'),
                                    ];
                                }); 
                })
                ->onlyOnDetail(),    

            BelongsTo::make('Restaurant Type', 'type', RestaurantType::class),

            Text::make('Branch Status', 'branching'),

            BelongsTo::make('Chain', 'chain', Restaurant::class),

            Map::make('Service Areas', 'areas')
                ->using(function($attribute) {
                    return Collection::make($attribute)->properties(function() {
                        return [
                            Text::make('Name'),

                            Integer::make('Duration', 'pivot->duration'),

                            Number::make('Cost', 'pivot->cost'),

                            Text::make('Note', 'pivot->note'),
                        ];
                    });
                }),

            Map::make('Working Hours', 'working_hours')
                ->using(function($attribute) {
                    dd(func_get_args());
                    return Collection::make($attribute, $value)->properties(function() { 
                        return [
                            Text::make('Name'),

                            Integer::make('Duration', 'pivot->duration'),

                            Number::make('Cost', 'pivot->cost'),

                            Text::make('Note', 'pivot->note'),
                        ];
                    });
                }),

        ];
    }  
}
