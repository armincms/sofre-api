<?php

namespace Armincms\SofreApi\Snail;
 
use Illuminate\Http\Request;
use Armincms\Snail\Http\Requests\SnailRequest;
use Armincms\Snail\Properties\{ID, Text};  
use Armincms\Snail\Snail;  

class City extends Schema
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \Armincms\Location\Location::class;  

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

            Text::make('Zones', 'zones')
                ->resolveUsing(function() { 
                    return Snail::path().'/'.Snail::currentVersion().'/zones?' . http_build_query([
                        'viaResource' => static::uriKey(),
                        'viaResourceId' => $this->id,
                        'viaRelationship' => 'zones'
                    ]);
                }),
        ];
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Armincms\Snail\Http\Requests\SnailRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(SnailRequest $request, $query)
    {
        return $query->city();
    }   
}
