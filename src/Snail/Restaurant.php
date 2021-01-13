<?php

namespace Armincms\SofreApi\Snail;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Armincms\Snail\Http\Requests\SnailRequest;
use Armincms\Snail\Properties\{ID, Text, Boolean, Integer, Number, Map, Collection, BelongsTo};
use Spatie\OpeningHours\OpeningHours;
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
    public static $model = \Armincms\Sofre\Models\Restaurant::class;  

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = [
        'type', 'areas', 'chain', 'foods.group', 'categories', 'discounts'
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

            Number::make('Packaging Cost'), 

            Map::make('Sending Method'), 

            Map::make('Payment Method'),

            Integer::make('Max Discount', function() {
                $maxPercent = $this->discounts->filter->isPercentage()->max('discount.value');
                $maxAmount  = $this->discounts->reject->isPercentage()->max('discount.value'); 

                $maxPerFood = $maxAmount / ($this->foods->min('pivot.price') ?: ($maxAmount ?: 1)) * 100;

                return ($maxPercent > $maxPerFood ? $maxPercent : $maxPerFood);
            }),

            Integer::make('Min Discount', function() {
                $minPercent = $this->discounts->filter->isPercentage()->min('discount.value');
                $minAmount  = $this->discounts->reject->isPercentage()->min('discount.value'); 

                $minPerFood = $minAmount / ($this->foods->max('pivot.price') ?: ($minAmount ?: 1)) * 100;

                return ($minPercent < $minPerFood ? $minPercent : $minPerFood);
            }),

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

            Map::make('Categories')->using(function($attribute) {
                return  Collection::make($attribute)->properties(function() {
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
                })
                ->onlyOnDetail(),


            Text::make('Comments', function() {
                return  Snail::path().'/'.Snail::currentVersion().'/comments?' . http_build_query([
                            'viaResource' => static::uriKey(),
                            'viaResourceId' => $this->id,
                            'viaRelationship' => 'comments'
                        ]);
            }),

            Map::make('Opening Hours', 'working_hours', function($value) {
                    return collect($value)->map(function($hours, $day) {
                        $hours = collect($hours)->map(function($data) use ($day) { 
                            $data['hours'] = $this->modifyMealHours($data['hours'], $data['data'], $day);

                            return $data; 
                        })->values()->all();

                        return compact('day', 'hours');
                    })->values();
                })
                ->using(function($attribute) {
                    return  Collection::make($attribute)->properties(function() {
                        return [
                            Text::make('Day'),

                            Map::make('Hours')->using(function($attribute) { 
                                return Collection::make($attribute)->properties(function() {
                                    return [
                                        Text::make('Data'),

                                        Text::make('Hours')
                                            ->nullable(),

                                        Text::make('From', 'hours', function($hours) {   
                                            return Str::before($hours, '-');
                                        })->nullable(),

                                        Text::make('To', 'hours', function($hours) {   
                                            return Str::after($hours, '-');
                                        })->nullable(), 
                                    ];
                                });
                            }),
                        ];
                    }); 
                })
                ->onlyOnDetail(), 

            Collection::make('Serving', function() {
                return $this->currentMeal();
            })->properties(function() {
                return [
                    Text::make('Meal', 'data')->nullable(),

                    Text::make('Hours', function($meal) {   
                        $today = Str::lower(now()->format('l'));

                        return $this->modifyMealHours($meal['hours'] ?? null, $meal['data'] ?? null, $today);
                    })->nullable(),

                    Text::make('From', function($meal) {   
                        $today = Str::lower(now()->format('l'));
                        $hours = $this->modifyMealHours($meal['hours'] ?? null, $meal['data'] ?? null, $today);

                        return Str::before($hours, '-');
                    })->nullable(),

                    Text::make('To', function($meal) {   
                        $today = Str::lower(now()->format('l'));
                        $hours = $this->modifyMealHours($meal['hours'] ?? null, $meal['data'] ?? null, $today);

                        return Str::after($hours, '-');
                    })->nullable(),

                    Boolean::make('Is Open', function($resource) { 
                        $hours = $this->filterHours($this->working_hours);

                        return OpeningHours::create($hours)->isOpenAt(now(config('app.timezone'))) && $this->isOpen();  
                    }),
                ];
            }),

        ];
    }

    public function modifyMealHours($hours = null, $meal, $day)
    { 
        if(! empty($hours) && $default = $this->defaultOpeningHours($day, $meal)) {
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
            static::$openingHours = $this->filterHours(Setting::openingHours());
        } 

        return collect(static::$openingHours[$day] ?? [])->where('data', $meal)->pluck('hours')->first();
    }

    public function isOpen()
    { 
        return OpeningHours::create($this->filterHours(Setting::openingHours()))->isOpenAt(now(config('app.timezone')));          
    } 

    public function currentMeal()
    {
         $today = Str::lower(now()->format('l'));

        return collect(data_get($this->filterHours(Setting::openingHours()), $today))->first(function($meal) {
            $hours = explode('-', data_get($meal, 'hours')); 
            $now = now(config('app.timezone'))->format('H:i');

            return count($hours) > 1 && $hours[0] < $now && $now  < $hours[1];
        });
    }

    public function filterHours($hours)
    {
        return collect($hours)->map(function($hours) {
            return collect($hours)->filter(function($hour) {
                return ! empty(data_get($hour, 'hours'));
            })->values();
        })->toArray();
    }
}
