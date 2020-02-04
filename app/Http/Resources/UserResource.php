<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
        return[
            'id' => $this->id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'username' => $this->username,
            'email' => $this->email,
            'profile_picture_name' => $this->profile_picture_name,
            'street' => $this->street,
            'house_number' => $this->house_number,
            'postcode' => $this->postcode,
            'residence' => $this->residence,
            'role' => $this->role,
            'email_verified_at' => $this->email_verified_at,
            'last_login' => $this->last_login,
            'last_logout' => $this->last_logout,
            'instagram_name' => $this->instagram_name
        ];
    }
}
