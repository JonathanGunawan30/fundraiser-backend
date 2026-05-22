<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;

class VerifyCampaignRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:approved,rejected',
        ];
    }
}
