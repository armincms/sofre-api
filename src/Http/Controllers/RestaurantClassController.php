<?php

namespace Armincms\SofreApi\Http\Controllers;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Armincms\Sofre\RestaurantClass; 

class RestaurantClassController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return RestaurantClass::get()->map(function($class) {
            return [
                'id'    => $class->id,
                'logo'   => $this->getConversions($class->getFirstMedia('logo'), [
                    'logo', 'thumbnail', 'icon'
                ]),
                'label' => $class->name, 
                'name'  => $class->name
            ];
        });
    } 


    public function getConversions($media, $conversions)
    { 
        $conversions = array_combine($conversions, $conversions);

        return collect($conversions)->map(function($conversion) use ($media) { 
            if(optional($media)->hasGeneratedConversion($conversion)) {
                return $media->getFullUrl($conversion);
            }

            return schema_placeholder($conversion); 
        });
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function restaurants(Request $request, $id)
    {
        $class = RestaurantClass::with(['restaurants' => function($q) {
            $q->with('media')->whereStatus('approved');
        }])->findOrFail($id);

        return new RestaurantCollection($class->restaurants);
    } 
}
