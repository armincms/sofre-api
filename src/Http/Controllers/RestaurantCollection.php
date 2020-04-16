<?php

namespace Armincms\SofreApi\Http\Controllers;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RestaurantCollection extends ResourceCollection
{ 
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    { 
        $currencies = currency()->getActiveCurrencies();

        return $this->collection->map(function($restaurant) use ($currencies) {
            return [
                'id' => $restaurant->id,
                'title' => $restaurant->name,
                'name' => $restaurant->name,
                'gallery' => $restaurant->getMedia('gallery')->map(function($media) {  
                    return $this->getConversions($media, ['main', 'thumbnail', 'icon']);
                }),
                'logo' => $restaurant->getMedia('logo')->map(function($media) {
                    return $this->getConversions($media, ['logo', 'thumbnail', 'icon']); 
                }),
                'material' => $restaurant->material,
                // 'video' => $restaurant->video,
                'status' => $restaurant->status,
                // 'latitude' => $restaurant->latitude,
                // 'longitude' => $restaurant->longitude,
                'sending_method' => $restaurant->sending_method,
                'payment_method' => $restaurant->payment_method,
                'min_order' => $restaurant->min_order,
                'currency' => option("_sofre_currency_", "IRR"),
                'url' => route('sofre.api.show', $restaurant),
                // 'working_hours' => $this->workingHours($restaurant),
                // 'menu'  => new RestaurantFoodCollection($restaurant->menu),
            ];
        })->all();
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
