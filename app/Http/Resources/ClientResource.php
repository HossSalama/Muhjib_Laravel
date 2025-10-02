<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'default_price_type' => $this->default_price_type,
            'logo' => image_url($this->logo),
            'status' => $this->status,
            'created_by_user_id' => $this->created_by_user_id,
        ];
    }
}
