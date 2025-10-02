<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BasketProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product->name_en ?? null,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'specification' => $this->product->specification ?? null,
            'main_image' => image_url($this->product->main_image),
        ];
    }
}
