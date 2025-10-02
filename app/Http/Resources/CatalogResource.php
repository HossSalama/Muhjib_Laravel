<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'template_id' => $this->template_id,

            // ✅ تحويل pdf_path لـ رابط كامل باستخدام الهيلبر
            'pdf_url'     => $this->pdf_path ? file_url($this->pdf_path) : null,

            // ✅ تحميل الريليشن لو موجود
            'basket'      => new BasketResource($this->whenLoaded('basket')),

            // ✅ جايب اسم الـ creator لو موجود
            'creator'     => $this->creator?->name,

            // ✅ فورمات للتاريخ
            'created_at'  => $this->created_at?->toDateTimeString(),

            // ✅ جايب المنتجات من الباسكت لو متحمل
            'products'    => $this->whenLoaded('basket', function () {
                return BasketProductResource::collection($this->basket->basketProducts);
            }),
        ];
    }
}