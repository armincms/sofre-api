<?php

namespace Armincms\SofreApi\Snail\Filters;

use Illuminate\Http\Request;
use Armincms\Snail\Filters\Filter;

class Location extends Filter
{ 
    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {  

        return $query->when(data_get($value, 'lat'), function($query) use ($value) {
            return $query->whereLatitude($value['lat']);
        })->when(data_get($value, 'long'), function($query) use ($value) {
            return $query->whereLatitude($value['long']);
        });
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [];
    }
}
