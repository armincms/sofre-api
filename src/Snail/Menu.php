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
        'food.group', 'restaurant.discounts', 'ratings'
    ];

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id'
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
                return $this->ratings->avg('rating');
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
                return $this->price ? $this->price() : 0;
            }), 

            Number::make('Discount', function() {
                $amount = $this->price ? $this->price() : 0;


                return intval($this->price - $amount) / ($this->price * 100 ?: 1); 
            }),  

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
                
            Text::make('Comments', function() {
                return  Snail::path().'/'.Snail::currentVersion().'/comments?' . http_build_query([
                            'viaResource' => static::uriKey(),
                            'viaResourceId' => $this->id,
                            'viaRelationship' => 'comments'
                        ]);
            }),
                
            BelongsTo::make('Restaurant', 'restaurant', Restaurant::class), 
        ];
    }  

    /**
     * Apply the search query to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected static function applySearch($query, $search)
    {   
        return parent::applySearch($query, $search)->orWhereHas('food', function($query) use ($search) {
            $query->where($query->qualifyColumn('name'), 'like', '%'.json_encode($search).'%');
        });
    }
}
