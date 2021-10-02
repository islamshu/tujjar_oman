<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (integer) $this->id,
            'product' => [
                'id'=>$this->product->id,
                'name_ar' => $this->product->name_ar,
                'name_en' => $this->product->name,
                'thumbnail_image' => api_asset($this->product->thumbnail_img),
                'base_price' => (double) $this->product->unit_price,
                'base_discounted_price' => (double) getPrice($this->product),
                'unit' => $this->product->unit,
                'rating' => (double) $this->product->rating,
                'min_qty' => $this->product->min_qty,
                'colors' => json_decode($this->product->colors),
                'size' => $this->product->choice_options ? $this->convertToSize(json_decode($this->product->choice_options)) : null,
                'fabric' => $this->product->choice_options ? $this->convertTofabric(json_decode($this->product->choice_options)) : null,
                'links' => [
                    'details' => route('v3.products.show', $this->product->id),
                    'reviews' => route('v3.api.reviews.index', $this->product->id),
                    'related' => route('v3.products.related', $this->product->id),
                    'top_from_seller' => route('v3.products.topFromSeller', $this->product->id)
                ]
            ]
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }

    protected function convertTosize($data)
    {
        $result = array();
        foreach ($data as $key => $choice) {
            if ($choice->attribute_id == 1) {
                $result = $choice->values;
            }
        }
        return $result;
    }

    protected function convertTofabric($data)
    {
        $result = array();
        foreach ($data as $key => $choice) {
            if ($choice->attribute_id == 2) {
                $result = $choice->values;
            }
        }
        return $result;
    }
}
