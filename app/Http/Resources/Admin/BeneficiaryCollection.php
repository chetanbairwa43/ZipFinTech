<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Carbon\Carbon;
class BeneficiaryCollection extends ResourceCollection
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
                'id'    => $data->id,
                'bank_name'    => $data->bank_name,
                'bank_code'    => $data->bank_code,
                'destination_address'    => $data->destination_address,
                'first_name'    => $data->first_name,
                'account_holder_name'    => $data->account_holder_name,
                'business_id'    => $data->business_id,
                'created_at'    => Carbon::parse($data->created_at)->format('Y-m-d'),
            ];
        });
    }
}