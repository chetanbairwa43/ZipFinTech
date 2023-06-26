<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Helper\Helper;

class VendorOrderListCollection extends ResourceCollection
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
                'id'            => $data->id,
                'amount'        => (string)$data->item_total,
                'date'          => $data->created_at->format('d F,Y - H:iA'),
                'status'        => (string)Helper::orderStatus()[$data->status],
            ];
        });
    }
}
