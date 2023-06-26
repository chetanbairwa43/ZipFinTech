<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;

class LatestProductCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return $this->collection->map(function($data) {
            return [
                'id'             => $data->id,
                'name'           => $data->product->name ?? '',
                'image'          => !empty($data->image) ? url(config('app.vendor_product_image').'/'.$data->image) : '',
                'varints'        => new VariantsProductsCollection($data->variants),
            ];
        });
    }
}
