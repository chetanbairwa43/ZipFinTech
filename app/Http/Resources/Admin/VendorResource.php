<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'           =>  $this->id,
            'store_name'  =>  (string)$this->store_name,
            'store_image' =>  !empty($this->store_image) ? url(config('app.vendor_document').'/'.$this->store_image) : '', 
        ];
    }
}
