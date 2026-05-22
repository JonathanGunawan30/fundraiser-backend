<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'category' => new CampaignCategoryResource($this->whenLoaded('category')),
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'story' => $this->story,
            'cover_image_url' => $this->cover_image_url,
            'goal_amount' => $this->goal_amount,
            'collected_amount' => $this->collected_amount,
            'donor_count' => $this->donor_count,
            'deadline' => $this->deadline?->toDateString(),
            'status' => $this->status,
            'verified_status' => $this->verified_status,
            'verified_at' => $this->verified_at?->toDateTimeString(),
            'verifier' => $this->whenLoaded('verifier', function() {
                return [
                    'id' => $this->verifier->id,
                    'name' => $this->verifier->name,
                ];
            }),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'images' => CampaignImageResource::collection($this->whenLoaded('images')),
            'updates' => CampaignUpdateResource::collection($this->whenLoaded('updates')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
