<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Helper\Helper;
class AssignedOrdersCollection extends ResourceCollection
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
                'order_id'              => $data->id,
                'payment_method'        => ($data->order_type == 'C') ? "COD" : "Online",
                'order_total'           => $data->grand_total,
                'order_status'          => Helper::orderStatus()[$data->status] ?? '',
                'date'                  => $data->created_at->format('l, d F Y h:i A'),
                'location'              => $data->orderAddress,
                'vendor_location'       => (isset($data->vendor) && isset($data->vendor->vendor)) ? new VendorProfileResource($data->vendor->vendor) : null,
            ];
        });
    }
}
