<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CartCollection extends ResourceCollection
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
                'id'                 => $data->id,
                'product_id'         => $data->getProductData->id,
                'variant_id'         => $data->getVariantData->id ?? '',
                'name'               => $data->getProductData->product->name ?? '',
                'variant_qty'        => $data->getVariantData->variant_qty ?? '',
                'variant_qty_type'   => $data->getVariantData->variant_qty_type ?? '',
                'min_qty'            => $data->getVariantData->min_qty ?? '',
                'max_qty'            => $data->getVariantData->max_qty ?? '',
                'variant_price'      => $data->getVariantData->price ?? '',
                'cart_item_qty'      => $data->qty ?? '',
                'total_price'        => (($data->qty)*($data->getVariantData->price) ?? ''),
                'image'              => !empty($data->getProductData->image) ? url(config('app.vendor_product_image').'/'.$data->getProductData->image) : ''

            ];
        });
    }
}
