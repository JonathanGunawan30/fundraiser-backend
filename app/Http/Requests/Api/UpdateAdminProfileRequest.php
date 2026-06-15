<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Auth;

class UpdateAdminProfileRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin-api')->check();
    }

    public function rules(): array
    {
        $adminId = Auth::guard('admin-api')->id();

        return [
            'name' => 'required|string|max:100',
            'email' => "required|string|email|max:100|unique:admins,email,{$adminId}",
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:1024',
        ];
    }
}
