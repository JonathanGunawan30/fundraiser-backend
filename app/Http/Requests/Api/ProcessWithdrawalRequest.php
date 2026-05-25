<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;

class ProcessWithdrawalRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:completed,rejected',
            'transfer_proof' => 'required_if:status,completed|image|mimes:jpeg,png,jpg,webp|max:2048',
            'rejection_reason' => 'required_if:status,rejected|string|max:500',
        ];
    }
}
