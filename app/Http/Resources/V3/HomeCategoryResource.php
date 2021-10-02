<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class HomeCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        if (!$this->category) {
            return [];
        }
        return [
            'name' => $this->category->name,
            'banner' => api_asset($this->category->banner),
            'icon' => api_asset($this->category->icon),
            'links' => [
                'products' => route('v3.api.products.category', $this->category->id),
                'sub_categories' => route('v3.subCategories.index', $this->category->id)
            ]
        ];
    }

    public function with($request)
    {
        return ['success' => true, 'status' => 200];
    }
}
