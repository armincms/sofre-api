<?php

namespace Armincms\SofreApi\Http\Controllers;

use Illuminate\Http\Resources\Json\Resource;

class RestaurantResource extends Resource
{ 

    /**
     * The "data" wrapper that should be applied.
     *
     * @var string
     */
    public static $wrap = null;
    
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    { 
        $currencies = currency()->getActiveCurrencies();

        return [
                'id'             => $this->id,
                'title'          => $this->name,
                'name'           => $this->name,
                'gallery'        => $this->getMedia('gallery')->map(function($media) {  
                    return $this->getConversions($media, ['main', 'thumbnail', 'icon']);
                }),
                'logo'           => $this->getConversions($this->getFirstMedia('logo'), [
                    'logo', 'thumbnail', 'icon'
                ]),
                'material'       => $this->material,
                'video'          => $this->video,
                'status'         => $this->status,
                'latitude'       => $this->latitude,
                'longitude'      => $this->longitude,
                'sending_method' => $this->sending_method,
                'payment_method' => $this->payment_method,
                'restaurant_class'          => $this->restaurantClass->label,
                'categories'     => optional($this->categories)->map(function($category) {
                    return [
                        'id'    => $category->id,
                        'name'  => $category->name,
                        'image' => $category->getMedia('image')->map(function($media) {
                                    return $this->getConversions($media, [
                                        'logo', 'thumbnail', 'icon'
                                    ]); 
                                }),
                    ];
                }),
                'min_order'      => floatval($this->min_order),
                'currency'       => "IRR", 
                'serving_start'  => $this->serving_start,
                'serving_end'    => $this->serving_end, 
                'working_hours'  => $this->workingHours(), 
                'service_range'  => $this->service_range,
                'menu'           => $this->menu,
            ];
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
}
