<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;
use App\Models\CampaignUpdate;

class UpdateCampaignUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $updateId = $this->route('campaign_update');
        $update = CampaignUpdate::find($updateId);
        
        return $update && $update->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}
