<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderItemCollection extends ResourceCollection
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
                'product_id'     => $data->product_id ?? '',
                'product_name'   => $data->products->name ?? '',
                'variant_id'     => $data->variant_id ?? '',
                'price'          => $data->price ?? '',
                'item_qty'       => $data->item_qty ?? '',
                'qty'            => $data->qty ?? '',
                'total_price'    => ($data->price)*($data->qty) ,
                'status'         => (string)$data->status,
            ];
        });
    }
}
