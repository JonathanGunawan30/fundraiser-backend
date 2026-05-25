<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;

class StoreDonationRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_id' => 'required|exists:campaigns,id',
            'amount' => 'required|numeric|min:1000',
            'message' => 'nullable|string|max:500',
            'is_anonymous' => 'boolean',
        ];
    }
}
