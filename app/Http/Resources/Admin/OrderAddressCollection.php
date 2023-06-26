<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderAddressCollection extends ResourceCollection
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
                'user_id'            => $data->user_id,
                'latitude'           => $data->latitude,
                'longitude'          => $data->longitude,
                'location'           => $data->location,
                'flat_no'            => $data->flat_no,
                'street'             => $data->street,
                'landmark'           => $data->landmark,
                'address_type'       => $data->address_type,
            ];
        });
    }
}
