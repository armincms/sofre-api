<?php

namespace Armincms\SofreApi\Snail;

use Armincms\Snail\Http\Requests\SnailRequest;
use Illuminate\Http\Request;
use Armincms\Snail\Properties\{ID, Text, BelongsTo};  

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
        ];
    }  
}
