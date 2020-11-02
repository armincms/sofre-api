<?php

namespace Armincms\SofreApi\Snail;

use Armincms\Snail\Http\Requests\SnailRequest;
use Illuminate\Http\Request;
use Armincms\Snail\Properties\{ID, Text, Boolean, Integer, Number, Map, Collection, BelongsTo};
use Armincms\Sofre\Helper;  
use Armincms\Snail\Snail;  

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
        'type', 'areas', 'chain', 'foods.group', 'categories'
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

            Collection::make('Image', function($resource) {
                    return $resource->getConversions($resource->getFirstMedia('image'), [
                        'restaurant-mobile', 'common-thumbnail'
                    ]);     
                })
                ->properties(function() {
                    return [
                        Text::make('Thumbnail', 'common-thumbnail')->nullable(true, ['']),
                        
                        Text::make('Noobar', 'restaurant-mobile')->nullable(true, ['']),
                        
                        Text::make('Main', 'restaurant-mobile')->nullable(true, ['']),
                    ];
                }),

            Collection::make('Logo', function($resource) {
                    return $resource->getConversions($resource->getFirstMedia('logo'), [
                        'restaurant-logo', 'common-thumbnail'
                    ]);     
                })
                ->properties(function() {
                    return [
                        Text::make('Thumbnail', 'restaurant-logo')->nullable(true, ['']),
                        
                        Text::make('Noobar', 'restaurant-logo')->nullable(true, ['']),
                        
                        Text::make('Main', 'restaurant-logo')->nullable(true, ['']),
                    ];
                }),

            Map::make('Categories')
                ->using(function($attribute) {
                    return  Collection::make($attribute)
                                ->properties(function() {
                                    return [
                                        Integer::make('CategoryId', 'id'),

                                        Text::make('Title', 'name'),
                                    ];
                                });
                }), 

            Number::make('Courier Cost', function($resource) {
                    if($resource->areas->count()) {
                        return $resource->areas->sum('pivot.cost') / $resource->areas->count();
                    } 
                })
                ->nullable(),

            Map::make('Menu', 'foods') 
                ->resolveUsing(function($foods) {  
                    return $foods->filter(function($food) {
                        return ! empty(data_get($food->pivot, strtolower(now()->format('l'))));
                    })->groupBy('food_group_id')->values();
                })
                ->using(function($attribute) {
                    return Collection::make($attribute)->properties(function() {
                        return [ 
                            Text::make('Group', function($resource) {
                                return $resource->first()->group->name;
                            }),

                            Integer::make('GroupId', function($resource) {
                                return $resource->first()->group->id;
                            }),

                            Map::make('Foods', function($resource) {
                                    return $resource->sortBy('pivot.order');
                                })
                                ->using(function($attribute) { 
                                    return  Collection::make($attribute)->properties(function() {
                                        return [
                                            ID::make(),

                                            Text::make('Name'),

                                            Number::make('Price', 'pivot->price'),

                                            Integer::make('Duration', 'pivot->duration'),

                                            Boolean::make('Available', 'pivot->duration'),

                                            Collection::make('Image', function($resource) {
                                                    return $resource->getConversions($resource->getFirstMedia('image'), [
                                                        'food-thumbnail', 'food-medium'
                                                    ]);
                                                })
                                                ->properties(function() {
                                                    return [
                                                        Text::make('Thumbnail', 'food-thumbnail')->nullable(true, ['']),
                                                        
                                                        Text::make('Noobar', 'food-medium')->nullable(true, ['']), 
                                                    ];
                                                }),
                                        ];
                                    }); 
                                }),
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


            Text::make('Comments', function() {
                return  Snail::path().'/'.Snail::currentVersion().'/comments?' . http_build_query([
                            'viaResource' => static::uriKey(),
                            'viaResourceId' => $this->id,
                            'viaRelationship' => 'comments'
                        ]);
            }),

            // Map::make('Working Hours', 'working_hours')
            //     ->using(function($attribute) {
            //         dd(func_get_args());
            //         return Collection::make($attribute, $value)->properties(function() { 
            //             return [
            //                 Text::make('Name'),

            //                 Integer::make('Duration', 'pivot->duration'),

            //                 Number::make('Cost', 'pivot->cost'),

            //                 Text::make('Note', 'pivot->note'),
            //             ];
            //         });
            //     }),

        ];
    }   
}
