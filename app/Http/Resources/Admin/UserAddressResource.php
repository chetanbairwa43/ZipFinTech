<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class UserAddressResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {   
      
            return [
                'id'                 => $this->id,
                'street_name'        => $this->street_name,
                'house_number'       => $this->house_number,
                'additional'         => $this->additional ?? "",
                'postal_code'        => $this->postal_code,
                'state'       => $this->state,
                'city'               => $this->city,
                'country'            => $this->country,
            ];
    }
}
