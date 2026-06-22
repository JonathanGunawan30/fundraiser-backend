<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $properties = $this->properties;
        
        // spatie/laravel-activitylog v5 stores model attribute changes in attribute_changes.
        // If properties is empty, fall back to attribute_changes.
        if (
            ($properties === null || 
            (is_countable($properties) && count($properties) === 0) || 
            (method_exists($properties, 'isEmpty') && $properties->isEmpty())) && 
            !empty($this->attribute_changes)
        ) {
            $attributeChanges = $this->attribute_changes;
            if (is_string($attributeChanges)) {
                $attributeChanges = json_decode($attributeChanges, true);
            }
            $properties = $attributeChanges;
        }

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
            'properties' => $properties,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
