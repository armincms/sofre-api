<?php

namespace Armincms\SofreApi\Snail;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Armincms\Snail\Http\Requests\SnailRequest;
use Armincms\Snail\Properties\{ID, Text, Boolean, Integer, Number, Map, Collection, BelongsTo};
use Armincms\Sofre\Nova\Setting;  
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
     * Default opening hours.
     * 
     * @var array
     */
    public static $openingHours = [];

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

                                            Map::make('Material', function($resource) {
                                                    return collect($resource->material)->map(function($value, $name) {
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

                                            Number::make('Old Price', 'pivot->price'),

                                            Number::make('Price', 'pivot->price')
                                                ->displayUsing(function($value, $resource, $attribute) {
                                                    return $this->discounts->applyOn($resource); 
                                                }), 

                
                                            Text::make('Comments', function($resource) {
                                                return  Snail::path().'/'.Snail::currentVersion().'/comments?' . http_build_query([
                                                            'viaResource' => Food::uriKey(),
                                                            'viaResourceId' => $resource->id,
                                                            'viaRelationship' => 'comments'
                                                        ]);
                                            }),

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

            Collection::make('Opening Hours', 'working_hours')
                ->properties(function() {
                    return collect(Helper::days())->map(function($label, $day) {
                        return Map::make($day)->using(function($attribute) {
                            return Collection::make($attribute)->properties(function() {
                                return [
                                    Text::make('Data'),

                                    Text::make('Hours'),
                                ];
                            });
                        }); 
                    })->all();
                }),

        ];
    }

    public function modifyMealHours($hours, $meal, $day)
    {
        if($default = $this->defaultOpeningHours($day, $meal)) {
            list($fromDefault, $toDefault) = explode('-', $default); 
            list($from, $to) = explode('-', $hours);

            if($fromDefault > $from) {
                $hours = Str::before($default, '-').'-'.Str::after($hours, '-');
            }

            if(str_replace('00:00', '24:00', $toDefault)  < $to) {
                $hours = Str::before($hours, '-').'-'.Str::after($default, '-');
            }
        }

        return $hours;
    }   

    public function defaultOpeningHours($day, $meal)
    {
        if(! isset(static::$openingHours)) {
            static::$openingHours = Setting::openingHours();
        } 

        return collect(static::$openingHours[$day] ?? [])->where('data', $meal)->pluck('hours')->first();
    }
}
