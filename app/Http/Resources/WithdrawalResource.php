<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign' => [
                'id' => $this->campaign->id,
                'title' => $this->campaign->title,
            ],
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'amount' => $this->amount,
            'bank_info' => [
                'bank_name' => $this->bank_name,
                'account_number' => $this->account_number,
                'account_name' => $this->account_name,
            ],
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'transfer_proof_url' => $this->transfer_proof_url,
            'processed_at' => $this->processed_at?->toDateTimeString(),
            'processed_by' => $this->whenLoaded('processor', function() {
                return [
                    'id' => $this->processor->id,
                    'name' => $this->processor->name,
                ];
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
