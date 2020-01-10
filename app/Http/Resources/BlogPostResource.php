<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
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
            'heading' => $this->heading,
            'previewContent' => $this->previewContent,
            'postContent' => $this->postContent,
            'imageName' => $this->imageName,
            'created_at' => $this->created_at
        ];
    }
}
