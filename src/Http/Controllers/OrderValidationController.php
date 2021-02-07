<?php

namespace Armincms\SofreApi\Http\Controllers;
 
use App\Http\Controllers\Controller; 
use Armincms\SofreApi\Http\Requests\OrderRequest;

class OrderValidationController extends Controller
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
    public function handle(OrderRequest $request)
    {   
        $request->validateOrder();   

        return $request->findRestaurant()->load('menus.food')->menus->filter(function($menu) use ($request) {
            return in_array($menu->id, array_keys((array) $request->get('items')));
        })->map(function($menu) {
            return [
                'Name'  => $menu->name(),
                'Price' => $menu->salePrice(),
                'ID'    => $menu->id
            ];
        })->values();
    }  
}
