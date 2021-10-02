<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class SearchProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name' => $this->name,
            'thumbnail_image' => api_asset($this->thumbnail_img),
            'base_price' => (double) $this->unit_price,
            'base_discounted_price' => (double) getPrice($this),
            'rating' => (double) $this->rating,
            'links' => [
                'details' => route('v3.products.show', $this->id),
                'reviews' => route('v3.api.reviews.index', $this->id),
                'related' => route('v3.products.related', $this->id),
                'top_from_seller' => route('v3.products.topFromSeller', $this->id)
            ]
        ];
    }

    public function with($request)
    {
        return ['success' => true, 'status' => 200];
    }
}
