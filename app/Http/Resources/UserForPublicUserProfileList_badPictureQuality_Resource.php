<?php

namespace App\Http\Resources;

use App\Climatemaster;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;


class UserForPublicUserProfileList_badPictureQuality_Resource extends JsonResource
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
            $image = Image::make(Storage::get("/images/profilePictures/" . $this->profile_picture_name));
            //Resize the image -> send data more fast to the client
            $image->resize(null, 40, function ($constraint) {
                $constraint->aspectRatio();
            });
            //blur -> looks better, obvious, that it needs to load data
            $image->blur();
            $image = $image->encode()->encoded;
        }else{
            $image = null;
        }

        //get climatemasterstatus
        $climatemaster_state = 'none';
        $climatemaster = Climatemaster::where('year', Carbon::now()->year)->where('user_id', $this->id)->first();
        if($climatemaster != null){
            //check if climatemaster
            if($climatemaster->verified == true){
                $climatemaster_state = 'climatemaster';
            }
            else{
                //get latest co2 calculation
                $co2calculation = $climatemaster->co2calculations()->latest()->first();
                if($co2calculation != null){
                    if($co2calculation->total_emissions <= 9){
                        $climatemaster_state = 'climatemaster_starter';
                    }
                }
            }
        }

        // return parent::toArray($request);
        return[
            'id' => $this->id,
            'username' => $this->username,
            'profile_picture_base64' => base64_encode($image),
            'climatemaster_state' => $climatemaster_state
        ];
    }
}
