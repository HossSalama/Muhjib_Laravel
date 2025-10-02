<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'basket' => new BasketResource($this->whenLoaded('basket')),
            'template_id' => $this->template_id,
            'pdf_url' => image_url($this->pdf_path),
            'creator' => $this->creator ? $this->creator->name : null,
            'created_at' => $this->created_at,
            'products' => BasketProductResource::collection(
                $this->basket->basketProducts ?? []
            ),
        ];
    }
}
