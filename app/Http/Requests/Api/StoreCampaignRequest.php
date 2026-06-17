<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Str;

class StoreCampaignRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('title') && !$this->has('slug')) {
            $this->merge([
                'slug' => Str::slug($this->title) . '-' . Str::random(5),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:campaign_categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:campaigns,slug',
            'description' => 'required|string|max:500',
            'story' => 'required|string',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'goal_amount' => 'required|numeric|min:10000',
            'deadline' => 'required|date|after_or_equal:today',
            'status' => 'nullable|string|in:draft,pending',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}
