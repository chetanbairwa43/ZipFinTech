<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Carbon\Carbon;
use App\Helper\Helper;
class VendorAvailabilityCollection extends ResourceCollection
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
                'week_day'       => Helper::dayFromNumber($data->week_day),
                'start_time'     => (string)$data->start_time,
                'end_time'       => (string)$data->end_time,
                'status'         => (boolean)$data->status,
            ];
        });
    }
}
