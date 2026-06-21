<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'donation_number' => $this->donation_number,
            'campaign' => [
                'id' => $this->campaign->id,
                'title' => $this->campaign->title,
                'slug' => $this->campaign->slug,
            ],
            'amount' => $this->amount,
            'message' => $this->message,
            'is_anonymous' => $this->is_anonymous,
            'status' => $this->status,
            'invoice_url' => $this->invoice_url,
            'payment' => [
                'snap_token' => $this->payment?->snap_token,
                'payment_url' => $this->payment?->payment_url,
                'status' => $this->payment?->status,
            ],
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
