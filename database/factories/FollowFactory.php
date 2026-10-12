<?php

namespace Database\Factories;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Follow>
 */
class FollowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'follower_id' => User::factory(),
            'following_id' => User::factory(),
            'status' => 'accepted',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create a pending follow (for private accounts).
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Create a follow for specific users.
     */
    public function forUsers(User $follower, User $following): static
    {
        return $this->state(fn (array $attributes) => [
            'follower_id' => $follower->id,
            'following_id' => $following->id,
        ]);
    }

    /**
     * Create an accepted follow.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
        ]);
    }
}
