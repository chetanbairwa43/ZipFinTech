<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class DriverInformationResource extends JsonResource
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
            'dob'                         => $this->dob,
            'aadhar_no'                   => $this->aadhar_no,
            'pan_no'                      => $this->pan_no,
            'vehicle_no'                  => $this->vehicle_no,
            'licence_no'                  => $this->licence_no,
            'bank_statement'              => !empty($this->bank_statement) ? url(config('app.driver_document').'/'.$this->bank_statement) : '',
            'pan_card_image'              => !empty($this->pan_card_image) ?  url(config('app.driver_document').'/'.$this->pan_card_image) : '',
            'aadhar_front_image'          => !empty($this->aadhar_front_image) ? url(config('app.driver_document').'/'.$this->aadhar_front_image) : '',
            'aadhar_back_image'           => !empty($this->aadhar_back_image) ?  url(config('app.driver_document').'/'.$this->aadhar_back_image) : '',
            'licence_front_image'         => !empty($this->licence_front_image) ? url(config('app.driver_document').'/'.$this->licence_front_image) : '',
            'licence_back_image'          => !empty($this->licence_back_image) ?  url(config('app.driver_document').'/'.$this->licence_back_image) : '',
        ];
    }
}
