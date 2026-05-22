<?php

namespace Database\Factories;

use App\Models\CampaignCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CampaignCategoryFactory extends Factory
{
    protected $model = CampaignCategory::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'icon_url' => $this->faker->imageUrl(),
            'is_active' => true,
            'order_index' => $this->faker->numberBetween(0, 100),
        ];
    }
}
