<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMainCategoriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categories' => 'required|array|min:1',
            'categories.*.brand_id' => 'required|exists:brands,id',
            'categories.*.name_en' => 'required|string|max:255',
            'categories.*.name_ar' => 'required|string|max:255',
            'categories.*.image_url' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'categories.*.color_code' => 'nullable|string|max:7',
        ];
    }
}


