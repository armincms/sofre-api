<?php

namespace Armincms\SofreApi\Snail\Orderings;

use Illuminate\Http\Request;
use Armincms\Snail\Orderings\Ordering;

class Longitude extends Ordering
{   
    /**
     * Apply the order to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $direction)
    {      
        return $query->orderBy('longitude', $direction);
    } 
}
