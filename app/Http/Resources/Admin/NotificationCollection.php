<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationCollection extends ResourceCollection
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
                'id'                => $data->id,
                'title'             => $data->title ?? '',
                'body'              => $data->body ?? '',
                'notification_type' => $data->notification_type ?? '',
                'seen'              => $data->seen ?? '',
                'time'              => $data->created_at->format('l, d F Y h:i A'),
            ];
        });
    }
}
