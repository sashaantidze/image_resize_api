<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\V1\AlbumResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class ImageManipulationResource extends JsonResource
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
            'name' => $this->name,
            'original' => URL::to($this->path),
            'type' => $this->type,
            'data' => $this->data,
            'resized' => URL::to($this->output_path),
            'album' => new AlbumResource($this->album),
            'created_at' => $this->created_at,
        ];
    }
}
