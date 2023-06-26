<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class VendorProfileResource extends JsonResource
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
            "id" => $this->id,
            "store_name" => (string)$this->store_name ?? '',
            'phone' => (string)!empty($this->vendor->phone) ? $this->vendor->phone : '',
            "storeImage" => (string)isset($this->store_image) ? url(config('app.vendor_document').'/'.$this->store_image) : '',
            "address" => (string)$this->address ?? '',
            "location" => (string)$this->location ?? '',
            "latitude" => (string)$this->lat ?? '',
            "longitude" => (string)$this->long ?? '',
            "aadharNo" => (string)$this->aadhar_no ?? '',
            "panNo" => (string)$this->pan_no ?? '',
            "delivery_range" => (string)$this->vendor->delivery_range ?? '',
            "bank_statement" => (string)isset($this->bank_statement) ? url(config('app.vendor_document').'/'.$this->bank_statement) : '',
            "pan_card_image" => (string)isset($this->pan_card_image) ? url(config('app.vendor_document').'/'.$this->pan_card_image) : '',
            "aadhar_front_image" => (string)isset($this->aadhar_front_image) ? url(config('app.vendor_document').'/'.$this->aadhar_front_image) : '',
            "aadhar_back_image" => (string)isset($this->aadhar_back_image) ? url(config('app.vendor_document').'/'.$this->aadhar_back_image) : '',
            "remark" => (string)$this->remark ?? '',
            "status" => (boolean)$this->status,
        ];
    }
}
