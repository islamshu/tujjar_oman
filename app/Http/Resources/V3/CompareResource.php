<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\V3\Product;

class CompareResource extends JsonResource
{
    public function toArray($request)
    {
        $product = Product::find($this->product_id);
        $dataa = [
            'id' => $this->id,
            'product' => [
                'name_ar' => @$product->name_ar,
                'name_en' => @$product->name,
                'thumbnail_image' => api_asset($product->thumbnail_img),
                'base_price' => single_price_api((double)$product->unit_price),
                'base_discounted_price' => single_price_api((double)getPrice($product)),
                'brand' => @$product->name,
            ],
            'links' => [
                'details' => route('v3.products.show', $this->product_id),
                'delete' => route('v3.api.delete_compare', $this->id)
            ]
        ];
        $dataa['reset'] = route('v3.api.reset_compare');
        return $dataa;
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
