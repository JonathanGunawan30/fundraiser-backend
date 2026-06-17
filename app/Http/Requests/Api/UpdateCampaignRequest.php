<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;

class UpdateCampaignRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('campaign');

        return [
            'category_id' => 'sometimes|required|exists:campaign_categories,id',
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:campaigns,slug,' . $id,
            'description' => 'sometimes|required|string|max:500',
            'story' => 'sometimes|required|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'goal_amount' => 'sometimes|required|numeric|min:10000',
            'deadline' => 'sometimes|required|date|after_or_equal:today',
            'status' => 'sometimes|required|string|in:draft,pending,active,completed,suspended',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}
