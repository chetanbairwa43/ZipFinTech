<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;

class BankDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = User::where('id',$this->user_id)->first();

        $decodedKYCInformation = json_decode($this->KYCInformation, true);

        $decodedKYCInformation['email'] = $user->email;
        $decodedKYCInformation['phoneNumber'] = $user->phone;

        $updatedKYCInformation = json_encode($decodedKYCInformation);

        return [
            'id' => $this->id,
            'user_id' => (string)$this->user_id,
            'accountType' => (string)$this->accountType,
            'currency' => $this->currency,
            'business' => (string)$this->business,
            'business_id' => (string)$this->business_id,
            'accountNumber' => $this->accountNumber,
            // 'KYCInformation' => json_decode($this->KYCInformation),
            'KYCInformation' => json_decode($updatedKYCInformation),
            'accountInformation' => json_decode($this->accountInformation),
            'card_type' => (string)$this->card_type,
            'creation_origin' => (string)$this->creation_origin,
        ];
    }
}
