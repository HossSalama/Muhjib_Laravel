<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name_en'         => $this->name_en,
            'name_ar'         => $this->name_ar,
            'description_ar'  => $this->description_ar,
            'features'        => $this->features,

            'main_colors' => collect($this->main_colors)->map(function ($color) {
                if (Str::startsWith($color, ['http://', 'https://'])) {
                    return $color;
                }
                if (Str::endsWith($color, ['.jpg', '.jpeg', '.png', '.webp'])) {
                    return image_url($color);
                }
                return $color;
            }),

            'brand_id'        => $this->brand_id,
            'sub_category_id' => $this->sub_category_id,
            'main_image'      => image_url($this->main_image),
            'pdf_hs'          => image_url($this->pdf_hs),
            'pdf_msds'        => image_url($this->pdf_msds),
            'pdf_technical'   => image_url($this->pdf_technical),

            'hs_code'         => $this->hs_code,
            'sku'             => $this->sku,
            'pack_size'       => $this->pack_size,
            'dimensions'      => $this->dimensions,
            'capacity'        => $this->capacity,
            'specification'   => $this->specification,

            'prices' => $this->whenLoaded('prices', function () {
                return $this->prices ? $this->prices->pluck('value', 'price_type') : [];
            }, []),

            'is_visible'  => $this->is_visible,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'quantity'    => $this->quantity,

            'certificates' => CertificateResource::collection($this->whenLoaded('certificates')),
            'legends'      => LegendResource::collection($this->whenLoaded('legends')),

            'images' => collect($this->images)->map(fn($img) => image_url($img)),
        ];
    }
}
