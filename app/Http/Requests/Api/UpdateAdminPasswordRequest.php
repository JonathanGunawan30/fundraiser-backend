<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Auth;

class UpdateAdminPasswordRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin-api')->check();
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}
