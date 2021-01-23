<?php

namespace Armincms\SofreApi\Http\Controllers;
 
use App\Http\Controllers\Controller;
use Armincms\Snail\Http\Requests\ResourceRequest;
use Armincms\Snail\Snail; 

class RatingStoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request 
     * @return \Illuminate\Http\Response
     */
    public function handle(ResourceRequest $request)
    {   
        $request->validate([
            'rating' => 'required|numeric'
        ]);  

        \Auth::loginUsingId(1);

        return tap($request->findResourceOrFail(), function($resource) use ($request) {
            $resource->rateOnce($request->get('rating'));
        })->resource;
    }  
}
