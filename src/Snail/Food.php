<?php

namespace Armincms\SofreApi\Snail;

use Armincms\Snail\Http\Requests\SnailRequest;
use Illuminate\Http\Request;
use Armincms\Snail\Properties\{ID, Text, Collection};  

class Food extends Schema
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \Armincms\Sofre\Food::class;  

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
        ];
    }  
}
