<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DeliveryAcceptanceCollection extends ResourceCollection
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
                'date'            => $data->created_at->format('l, d F Y h:i A'),
                'order_id'        => isset($data->order) ? $data->order->id : '',
                'payment_method'  => isset($data->order) ? (($data->order->order_type == 'C') ? "COD" : "Online") : '',
                'order_total'     => isset($data->order) ? $data->order->grand_total : '',
                'location'        => isset($data->order) ? $data->order->orderAddress : null,
                'vendor_location' => (isset($data->order) && isset($data->order->vendor) && isset($data->order->vendor->vendor)) ? new VendorProfileResource($data->order->vendor->vendor) : null,
            ];
        });
    }
}
