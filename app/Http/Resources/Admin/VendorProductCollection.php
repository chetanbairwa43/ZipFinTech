<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;

class VendorProductCollection extends ResourceCollection
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
                'id' => $data->id,
                "product" => new ProductResource($data->product),
                "image" => (string)isset($data->image) ? url(config('app.vendor_product_image').'/'.$data->image) : '',
                "status" => (boolean)$data->status,
                "variants" => new VariantsProductsCollection($data->variants),
            ];
        });
    }
}
