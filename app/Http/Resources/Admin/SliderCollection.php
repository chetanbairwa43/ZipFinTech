<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SliderCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return $this->collection->map(function($data) {
            return [
                'id'             => $data->id,
                'title'          => (string)$data->title,
                'link'           => (string)$data->link,
                'image'          => !empty($data->image) ? url(config('app.slider_image').'/'.$data->image) : '',
            ];
        });
    }
}
