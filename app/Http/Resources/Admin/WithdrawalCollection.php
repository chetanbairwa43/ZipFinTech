<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Carbon\Carbon;

class WithdrawalCollection extends ResourceCollection
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
                'amount'        => (string)$data->amount,
                'date'          => Carbon::parse($data->created_at)->format('d F,Y - H:i A'),
                'status'        => (string)$data->status,
            ];
        });
    }
}
