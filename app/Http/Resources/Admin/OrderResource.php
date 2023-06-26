<?php

namespace App\Http\Resources\Admin;
use App\Helper\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_id'                      => $this->id,
            'item_total'                    => $this->item_total ?? '0',
            'item_total'                    => $this->item_total ?? '0',
            'surcharge'                     => $this->surcharge ?? '0',
            'tax'                           => $this->tax ?? '0',
            'tax_1'                         => isset($this->tax_id_1) ? json_decode($this->tax_id_1) : null,
            'tax_2'                         => isset($this->tax_id_2) ? json_decode($this->tax_id_2) : null,
            'delivery_charges'              => $this->delivery_charges ?? '0',
            'packing_fee'                   => $this->packing_fee ?? '0',
            'tip_amount'                    => $this->tip_amount ?? '0',
            'coupon_discount'               => $this->coupon ?? null,
            'commission_driver'             => $this->commission_driver ?? '0',
            'commission_admin'              => $this->commission_admin ?? '0',
            'grand_total'                   => $this->grand_total ?? '0',
            'user'                          => isset($this->user) ? new UserResource($this->user) : null,
            'vendor'                        => (isset($this->vendor) && isset($this->vendor->vendor)) ? new VendorProfileResource($this->vendor->vendor) : null,
            'driver'                        => isset($this->driver) ? new UserResource($this->driver) : null,
            'address'                       => isset($this->orderAddress) ? $this->orderAddress : null,
            'order_type'                    => ($this->order_type=='C') ? 'COD' : 'Online', 
            'delivery_status'               => Helper::orderStatus()[$this->status] ?? '',
            'order_items'                   => new OrderItemCollection($this->orderItem),
            'placed_at'                     => $this->created_at->format('l, d F Y h:i A'),
        ];
    }
}
