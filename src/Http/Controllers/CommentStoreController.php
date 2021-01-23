<?php

namespace Armincms\SofreApi\Http\Controllers;
 
use App\Http\Controllers\Controller;
use Armincms\Snail\Http\Requests\ResourceRequest;
use Armincms\Snail\Snail; 

class CommentStoreController extends Controller
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
            'comment' => 'required|string'
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
