<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\V3\BusinessSetting;

class ClubPointResource extends JsonResource
{
    public function toArray($request)
    {
        $datae = [
            'id' => $this->id,
            'date' => date('d-m-Y', strtotime($this->created_at)),
            'points' => $this->points,
            'converted' => $this->convert_status == 1 ? 'yes' : 'no',
        ];
        if ($this->convert_status == 1) {
            $datae['links'] = 'no links';
        } else {
            $datae['links'] = route('api_convert_club', $this->id);
        }
        return $datae;
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
