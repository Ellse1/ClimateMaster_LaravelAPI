<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClimadviceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title,
            'shortDescription' => $this->shortDescription,
            'iconName' => $this->iconName,
            'easy' => $this->easy,
            'climateMasterArea' => $this->climateMasterArea
        ];
    }
}
