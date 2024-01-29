<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use App\Models\UserAddress;
use App\Models\UserMeta;
use Auth;
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
       $UserMeta = UserMeta::where('user_id', $this->id)->where('key','enable_security_lock')->first();
        return [
            'id' => $this->id,
            // 'latitude' => (string)$this->latitude,
            // 'longitude' => (string)$this->longitude,
            // 'location' => (string)$this->location,
            // 'name'  => (string)$this->fname.($this->lname)?' '.$this->lname:'',
            'fname'               => (string)$this->fname,
            'lname'               => (string)$this->lname,
            'email'               => (string)$this->email,
            'phone'               => (string)$this->phone,
            'zip_tag'             => (string)$this->zip_tag,
            'dob'                 => (string)$this->dob,
            'bvn'                 => (string)$this->bvn,
            'primary_purpose'     => (string)$this->primary_purpose ?? null,
            'gender'              => (string)$this->gender ?? null,
            'nationality'         => (string)$this->nationality ?? null,
            'birth_place'         => (string)$this->birth_place,
            'pin'                 => (string)$this->pin,
            'business_id'         => $this->virtual->business_id ?? '',
            'country_code'        => (string)$this->country_code,
            'profile_image'       => (string)isset($this->profile_image) ? url(config('app.profile_image').'/'.$this->profile_image) : '',
            'is_profile_complete' => boolval($this->is_profile_complete),
            'status'              => boolval($this->status),
            'is_africa_verifed'   => boolval($this->is_africa_verifed),
            'unique_id'           => $this->unique_id ?? null,
            'freshwork_id'        => $this->freshwork_id ?? null,
            'enable_security_lock'=> isset($UserMeta->value)  && $UserMeta->value == true ? true :false,
            'address'             => UserAddress::getAddressesByUser($this->id),
        ];
    }
}
