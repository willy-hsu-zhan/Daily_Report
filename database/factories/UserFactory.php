<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $emailSuffixes = ['exmaple.com.tw', 'gmail.com'];
        $emailSuffix   = $this->faker->randomElement($emailSuffixes);
        $namePrefix = 'test_';

        return [
            'name'              => $namePrefix . $this->faker->name,
            'email'             => $this->faker->unique()->userName . '@' . $emailSuffix,
            'email_verified_at' => now(),
            'password'          => bcrypt('password'),
            'remember_token'    => Str::random(10),
            'google_account'    => $this->faker->unique()->userName,
            'department'        => $this->faker->randomElement(['guest', 'td', 'ad', 'qa', 'csd', 'art', 'pd', 'pd&qa']),
            'admin'             => $this->faker->numberBetween(0, 1),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
