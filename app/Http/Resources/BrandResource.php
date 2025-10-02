<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'logo' => image_url($this->logo),
            'short_description_en' => $this->short_description_en,
            'short_description_ar' => $this->short_description_ar,
            'full_description_en' => $this->full_description_en,
            'full_description_ar' => $this->full_description_ar,
            'background_image_url' => image_url($this->background_image_url),
            'color_code' => $this->color_code,
            'catalog_pdf_url' => file_url($this->catalog_pdf_url),
            'main_categories' => $this->whenLoaded('mainCategories'),
            'created_at' => $this->created_at,
            'status' => $this->status,
        ];
    }
}
