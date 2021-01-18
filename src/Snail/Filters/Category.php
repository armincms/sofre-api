<?php

namespace Armincms\SofreApi\Snail\Filters;

use Illuminate\Http\Request;
use Armincms\Snail\Filters\Filter;

class Category extends Filter
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
        return $query->whereHas('categories', function($quer) use ($value) {
            $quer->whereKey((array) $value);
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
