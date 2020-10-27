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
                        'logo', 'thumbnail', 'icon'
                    ]);     
                })
                ->properties(function($attribute) {
                    return [
                        Text::make('Thumbnail')->nullable(true, ['']),
                        
                        Text::make('Noobar')->nullable(true, ['']),
                        
                        Text::make('Main')->nullable(true, ['']),
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
