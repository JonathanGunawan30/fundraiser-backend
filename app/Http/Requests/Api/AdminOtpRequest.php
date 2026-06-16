<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;
use App\Rules\Turnstile;

class AdminOtpRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:admins,email',
            'cf_turnstile_response' => ['required', new Turnstile],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'email.exists' => 'Admin account with this email not found.',
        ];
    }
}
