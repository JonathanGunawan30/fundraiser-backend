<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;

class UpdateCampaignCategoryRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('campaign_category');

        return [
            'name' => 'sometimes|required|string|max:100',
            'slug' => 'sometimes|required|string|max:100|unique:campaign_categories,slug,' . $id,
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:1024',
            'is_active' => 'boolean',
            'order_index' => 'integer',
        ];
    }
}
