<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'log_name' => $this->log_name,
            'description' => $this->description,
            'subject_type' => basename(str_replace('\\', '/', $this->subject_type)),
            'subject_id' => $this->subject_id,
            'causer' => $this->causer ? [
                'id' => $this->causer->id,
                'name' => $this->causer->name,
                'type' => basename(str_replace('\\', '/', $this->causer_type)),
            ] : null,
            'properties' => $this->properties,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
