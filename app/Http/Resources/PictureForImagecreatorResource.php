<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PictureForImagecreatorResource extends JsonResource
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
        if(Storage::exists("/images/pictures_for_imagecreator/" . $this->picture_name)){
            $image = Storage::get("/images/pictures_for_imagecreator/" . $this->picture_name);
        }else{
            $image = null;
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'picture_name' => $this->picture_name,
            'sharing_permitted' => $this->sharing_permitted,
            'picture_base64' => base64_encode($image)
        ];
    }
}
