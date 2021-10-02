<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class WalletCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'amount' => single_price_api($data->amount),
                    'payment_method' => $data->payment_method,
                    'approval' => $data->offline_payment ? ($data->approval == 1 ? "Approved" : "Decliend") : "N/A",
                     'date' => $data->created_at,
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
