<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Helper\Helper;

class WalletTransactionCollection extends ResourceCollection
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
                'id'                   => $data->id,
                'user_id'              => $data->user_id,
                'previous_balance'     => $data->previous_balance,
                'current_balance'      => $data->current_balance,
                'amount'               => $data->amount,
                'status'               => Helper::walletTransactionsStatus()[$data->status],
                'remark'               => $data->remark ?? '-',
                'transaction_date'     => $data->created_at->format('l, d F Y')
            ];
        });
    }
}
