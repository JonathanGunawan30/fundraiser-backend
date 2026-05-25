<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignUpdate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignUpdateFactory extends Factory
{
    protected $model = CampaignUpdate::class;

    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'user_id' => function (array $attributes) {
                return Campaign::find($attributes['campaign_id'])->user_id;
            },
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'image_url' => $this->faker->imageUrl(),
        ];
    }
}
