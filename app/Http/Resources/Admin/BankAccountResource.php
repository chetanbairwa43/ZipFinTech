<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountResource extends JsonResource
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
            'id' => $this->id,
            'bank' => (string)$this->bank->name,
            'account_name' => (string)$this->account_name,
            'account_no' => $this->account_no,
            'ifsc_code' => (string)$this->ifsc_code,
        ];
    }
}
