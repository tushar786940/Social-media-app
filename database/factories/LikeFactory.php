<?php

namespace Database\Factories;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Like>
 */
class LikeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create a like for specific user and post.
     */
    public function forUserAndPost(User $user, Post $post): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }
}
