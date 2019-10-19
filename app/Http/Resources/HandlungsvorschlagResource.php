<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HandlungsvorschlagResource extends JsonResource
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
            'titel' => $this->titel,
            'kurzbeschreibung' => $this->kurzbeschreibung,
            'iconName' => $this->iconName
        ];
    }
}