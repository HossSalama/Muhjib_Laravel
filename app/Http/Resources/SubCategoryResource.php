<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'main_category_id' => $this->main_category_id,
            'name_en'          => $this->name_en,
            'name_ar'          => $this->name_ar,
            'created_at'       => $this->created_at,

            'cover_image'      => image_url($this->cover_image),
            'background_image' => image_url($this->background_image),

            'has_cover_image'      => !empty($this->cover_image),
            'has_background_image' => !empty($this->background_image),
        ];
    }
}
