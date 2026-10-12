<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'bio' => fake()->sentence(),
            'location' => fake()->city(),
            'website' => fake()->url(),
            'birthdate' => fake()->dateTimeBetween('-50 years', '-18 years'),
            'is_private' => false,
            'profile_photo_path' => null,
            'cover_photo_path' => null,
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

    /**
     * Create a user with private profile.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_private' => true,
        ]);
    }

    /**
     * Create a user with profile photo.
     */
    public function withProfilePhoto(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_photo_path' => 'profile-photos/' . fake()->uuid() . '.jpg',
        ]);
    }

    /**
     * Create a user with cover photo.
     */
    public function withCoverPhoto(): static
    {
        return $this->state(fn (array $attributes) => [
            'cover_photo_path' => 'cover-photos/' . fake()->uuid() . '.jpg',
        ]);
    }

    /**
     * Create a user with posts.
     */
    public function withPosts(int $count = 3): static
    {
        return $this->afterCreating(function (User $user) use ($count) {
            Post::factory()->count($count)->forUser($user)->create();
        });
    }

    /**
     * Create a user with followers.
     */
    public function withFollowers(int $count = 3): static
    {
        return $this->afterCreating(function (User $user) use ($count) {
            $followers = User::factory()->count($count)->create();
            foreach ($followers as $follower) {
                Follow::factory()->forUsers($follower, $user)->accepted()->create();
            }
        });
    }

    /**
     * Create a user with following.
     */
    public function withFollowing(int $count = 3): static
    {
        return $this->afterCreating(function (User $user) use ($count) {
            $following = User::factory()->count($count)->create();
            foreach ($following as $follow) {
                Follow::factory()->forUsers($user, $follow)->accepted()->create();
            }
        });
    }

    /**
     * Create a user with likes on posts.
     */
    public function withLikes(int $count = 3): static
    {
        return $this->afterCreating(function (User $user) use ($count) {
            $posts = Post::factory()->count($count)->create();
            foreach ($posts as $post) {
                Like::factory()->forUserAndPost($user, $post)->create();
            }
        });
    }

    /**
     * Create a user with comments on posts.
     */
    public function withComments(int $count = 3): static
    {
        return $this->afterCreating(function (User $user) use ($count) {
            $posts = Post::factory()->count($count)->create();
            foreach ($posts as $post) {
                Comment::factory()->forUserAndPost($user, $post)->create();
            }
        });
    }
}
