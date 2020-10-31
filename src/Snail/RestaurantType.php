<?php

namespace Armincms\SofreApi\Snail;

use Armincms\Snail\Http\Requests\SnailRequest;
use Illuminate\Http\Request;
use Armincms\Snail\Properties\{ID, Text, Collection, BelongsToMany};  
use Armincms\Snail\Snail;  

class RestaurantType extends Schema
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \Armincms\Sofre\RestaurantType::class;  

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

            Collection::make('Image', function($resource) {
                    return $resource->getConversions($resource->getFirstMedia('image'), [
                        'restaurant-type-logo', 'common-thumbnail'
                    ]);     
                })
                ->properties(function() {
                    return [
                        Text::make('Thumbnail', 'common-thumbnail')->nullable(true, ['']),
                        
                        Text::make('Noobar', 'restaurant-type-logo')->nullable(true, ['']),
                        
                        Text::make('Main', 'restaurant-type-logo')->nullable(true, ['']),
                    ];
                }),

            Text::make('Restaurants', 'restaurants')
                ->resolveUsing(function() { 
                    return Snail::path().'/'.Snail::currentVersion().'/restaurants?' . http_build_query([
                        'viaResource' => static::uriKey(),
                        'viaResourceId' => $this->id,
                        'viaRelationship' => 'restaurants'
                    ]);
                }),
        ];
    }  
}
