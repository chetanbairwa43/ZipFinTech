<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;

class VariantsProductsCollection extends ResourceCollection
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
                'id'                   => $data->id,
                'vendor_product_id'    => $data->vendor_product_id ?? '',
                'market_price'         => $data->market_price ?? '',
                'price'                => $data->price ??'',
                'variant_qty'          => $data->variant_qty ?? '',
                'variant_qty_type'     => $data->variant_qty_type ?? '',
                'min_qty'             =>  $data->min_qty,
                'max_qty'             =>  $data->max_qty,
                'discount_off'         => !empty($data->off_price) ? $data->off_price.'% OFF' : '0'.'% OFF',
            ];
        });
    }
}
