<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;

class UpdateSiteSettingRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('site_setting');

        return [
            'key' => 'sometimes|required|string|max:100|unique:site_settings,key,' . $id,
            'value' => 'sometimes|required|string',
            'type' => 'sometimes|required|string|in:string,number,boolean,json',
        ];
    }
}
