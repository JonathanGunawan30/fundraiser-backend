<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;

class StoreSiteSettingRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => 'required|string|max:100|unique:site_settings,key',
            'value' => 'required|string',
            'type' => 'required|string|in:string,number,boolean,json',
        ];
    }
}
