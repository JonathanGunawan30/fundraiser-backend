<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;
use App\Models\Campaign;

class StoreCampaignUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $campaignId = $this->input('campaign_id');
        if (!$campaignId) return false;

        $campaign = Campaign::find($campaignId);
        return $campaign && $campaign->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'campaign_id' => 'required|exists:campaigns,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}
