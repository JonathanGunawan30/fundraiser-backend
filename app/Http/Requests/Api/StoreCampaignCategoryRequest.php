<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;

class StoreCampaignCategoryRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:campaign_categories,slug',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:1024',
            'is_active' => 'boolean',
            'order_index' => 'integer',
        ];
    }
}
