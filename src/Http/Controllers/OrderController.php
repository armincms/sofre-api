<?php

namespace Armincms\SofreApi\Http\Controllers;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Armincms\Snail\Snail; 

class OrderController extends Controller
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
    public function validate(Request $request)
    {   
        $request->validate([
            'sending_method' => 'required|string',
            'payment_method' => 'required|string',
            'address_id'     => 'required|numeric',
            'items.*'        => function() {
                dd(func_get_args());
            }
        ]);  

        return $request->findResourceOrFail()->comments()->firstOrCreate([
            'commenter_id'  => $request->user()->id,
            'commenter_type'=> $request->user()->getMorphClass(),
            'comment'   => $request->geT('comment'),
            'approved'  => 0,
            'child_id'  => intval($request->get('comment_id')) ?: null,
        ]);
    }  
}
