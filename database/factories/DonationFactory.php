<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DonationFactory extends Factory
{
    protected $model = Donation::class;

    public function definition(): array
    {
        return [
            'donation_number' => 'DON-' . strtoupper(Str::random(10)),
            'campaign_id' => Campaign::factory(),
            'user_id' => User::factory(),
            'amount' => $this->faker->numberBetween(10000, 1000000),
            'message' => $this->faker->sentence(),
            'is_anonymous' => $this->faker->boolean(20),
            'status' => 'pending',
        ];
    }
}
