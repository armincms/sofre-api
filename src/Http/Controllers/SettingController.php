<?php

namespace Armincms\SofreApi\Http\Controllers;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Armincms\Sofre\Nova\Setting; 

class SettingController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request 
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {  
        return [
            'Curreny'   => Setting::currency(), 
            'Tax'       => intval(Setting::option('_sofre_tax_')),
        ];
    }  
}
