<?php

namespace App\Http\Resources\Admin;
use App\Helper\Helper;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderCollection extends ResourceCollection
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
                'order_id'                      => $data->id,
                'item_total'                    => $data->item_total ?? '0',
                'surcharge'                     => $data->surcharge ?? '0',
                'tax'                           => $data->tax ?? '0',
                'tax_1'                         => isset($data->tax_id_1) ? json_decode($data->tax_id_1) : null,
                'tax_2'                         => isset($data->tax_id_2) ? json_decode($data->tax_id_2) : null,
                'delivery_charges'              => $data->delivery_charges ?? '0',
                'packing_fee'                   => $data->packing_fee ?? '0',
                'tip_amount'                    => $data->tip_amount ?? '0',
                'coupon_discount'               => $data->coupon ?? NULL,
                'commission_driver'             => $data->commission_driver ?? '0',
                'commission_admin'              => $data->commission_admin ?? '0',
                'grand_total'                   => $data->grand_total ?? '0',
                'user'                          => $data->user,
                'vendor'                        => $data->vendor,
                'driver'                        => $data->driver,
                'address'                       => $data->orderAddress,
                'order_type'                    => ($data->order_type=='C') ? 'COD' : 'Online', 
                'delivery_status'               => Helper::orderStatus()[$data->status] ?? '',
                'item_count'                    => count($data->orderItem),
                'order_items'                   => new OrderItemCollection($data->orderItem),
                'image'                         => url(config('app.order_image')),
                'placed_at'                     => $data->created_at->format('l, d F Y h:i A'),
            ];
        });
    }
}
