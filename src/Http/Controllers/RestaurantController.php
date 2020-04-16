<?php

namespace Armincms\SofreApi\Http\Controllers;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Armincms\Sofre\Restaurant; 

class RestaurantController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {  
        return new RestaurantResource(Restaurant::with([
            'media', 'categories', 'restaurantClass', 'workingDays'
        ])->findOrFail($id)); 
    }  
}
