<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class VendorProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            "vendor" => new UserResource($this->vendor),
            "product" => new ProductResource($this->product),
            "image" => (string)isset($this->image) ? url(config('app.vendor_product_image').'/'.$this->image) : '',
            "status" => (boolean)$this->status,
            "products_variant" => new VariantsProductsCollection($this->variants)
        ];
    }
}
