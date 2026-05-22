<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        $title = $this->faker->sentence();
        return [
            'user_id' => User::factory(),
            'category_id' => CampaignCategory::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(5),
            'description' => $this->faker->paragraph(),
            'story' => $this->faker->text(1000),
            'cover_image_url' => $this->faker->imageUrl(),
            'goal_amount' => $this->faker->numberBetween(100000, 10000000),
            'collected_amount' => 0,
            'donor_count' => 0,
            'deadline' => $this->faker->dateTimeBetween('+1 month', '+3 months'),
            'status' => 'pending',
            'verified_status' => 'pending',
        ];
    }
}
