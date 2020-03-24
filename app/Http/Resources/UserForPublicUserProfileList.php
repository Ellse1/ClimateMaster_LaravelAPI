<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserForPublicUserProfileList extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        //Check if this picture exists
        if(Storage::exists("/images/profilePictures/" . $this->profile_picture_name)){
            $image = Storage::get("/images/profilePictures/" . $this->profile_picture_name);
        }else{
            $image = null;
        }

        // return parent::toArray($request);
        return[
            'id' => $this->id,
            'username' => $this->username,
            'profile_picture_base64' => base64_encode($image),
            'public_profile_general_information' => $this->public_user_profile->information_general,
        ];
    }
}
