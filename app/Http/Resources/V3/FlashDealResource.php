<?php

namespace App\Http\Resources\V3;

use App\Models\V3\Product;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V3\ProductResource;

class FlashDealResource extends JsonResource
{
    public function toArray($request)
    {
        $products = collect();
        foreach ($this->flashDealProducts as $key => $flash_deal_product) {
            $product = Product::where('id',$flash_deal_product->product_id)->where(function ($qq){
                $qq->where('added_by','admin')->orWhereHas('user', function ($query) {
                    $query->whereHas('seller', function ($q) {
                        $q->where('verification_status',1);
                    });
                });
            })->first();
            if($product != null){
                $products->push($product);
            }
        }
        $arr = [
            'id' => $this->id,
            'title' => $this->title,
            'end_date' => $this->end_date,
        ];
        $arr['products']['data'] = ProductResource::collection($products);
        return $arr;
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
