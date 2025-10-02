<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MainCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'brand_id'   => $this->brand_id,
            'name_en'    => $this->name_en,
            'name_ar'    => $this->name_ar,
            'color_code' => $this->color_code,
            'image_url'  => image_url($this->image_url),
            'created_at' => $this->created_at,
        ];
    }
}
