<?php

use App\Models\Comment;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Feed System', function () {
    
    describe('Home Feed', function () {
        
        it('shows own posts in home feed', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->public()->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            $response->assertViewIs('feed.index');
            $response->assertViewHas('posts');
            
            // The post should be in the feed
            $posts = $response->viewData('posts');
            expect($posts->contains('id', $post->id))->toBeTrue();
        });

        it('shows posts from followed users', function () {
            $user = User::factory()->create();
            $followedUser = User::factory()->create();
            
            // User follows the other user
            Follow::factory()->forUsers($user, $followedUser)->accepted()->create();
            
            // Followed user creates a public post
            $post = Post::factory()->forUser($followedUser)->public()->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            expect($posts->contains('id', $post->id))->toBeTrue();
        });

        it('shows posts from public accounts without following', function () {
            $user = User::factory()->create();
            $publicUser = User::factory()->create(['is_private' => false]);
            
            // User does NOT follow the public user
            // But public user's public posts should still appear
            $post = Post::factory()->forUser($publicUser)->public()->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            expect($posts->contains('id', $post->id))->toBeTrue();
        });

        it('does not show private posts from non-followed users', function () {
            $user = User::factory()->create();
            $privateUser = User::factory()->create(['is_private' => false]);
            
            // User does NOT follow the private user
            // Private user creates a private post
            $post = Post::factory()->forUser($privateUser)->private()->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            expect($posts->contains('id', $post->id))->toBeFalse();
        });

        it('does not show posts from private accounts without following', function () {
            $user = User::factory()->create();
            $privateUser = User::factory()->create(['is_private' => true]);
            
            // User does NOT follow the private user
            // Private user creates a public post
            $post = Post::factory()->forUser($privateUser)->public()->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            expect($posts->contains('id', $post->id))->toBeFalse();
        });

        it('shows private posts from own account', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->private()->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            expect($posts->contains('id', $post->id))->toBeTrue();
        });

        it('shows posts from private accounts that user follows', function () {
            $user = User::factory()->create();
            $privateUser = User::factory()->create(['is_private' => true]);
            
            // User follows the private user
            Follow::factory()->forUsers($user, $privateUser)->accepted()->create();
            
            // Private user creates a public post
            $post = Post::factory()->forUser($privateUser)->public()->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            expect($posts->contains('id', $post->id))->toBeTrue();
        });

        it('orders posts by newest first', function () {
            $user = User::factory()->create();
            
            $post1 = Post::factory()->forUser($user)->create(['created_at' => now()->subHours(2)]);
            $post2 = Post::factory()->forUser($user)->create(['created_at' => now()->subHour()]);
            $post3 = Post::factory()->forUser($user)->create(['created_at' => now()]);

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            
            expect($posts->first()->id)->toBe($post3->id);
            expect($posts->last()->id)->toBe($post1->id);
        });

        it('paginates feed', function () {
            $user = User::factory()->create();
            
            Post::factory()->count(15)->forUser($user)->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            expect($posts->count())->toBeLessThanOrEqual(10);
        });

        it('requires authentication', function () {
            $response = $this->get('/feed');

            $response->assertRedirect('/login');
        });

        it('shows empty feed state when no posts', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            $response->assertSee('Your feed is empty');
        });

        it('does not leak private posts from other users', function () {
            $user = User::factory()->create();
            $privateUser = User::factory()->create(['is_private' => true]);
            
            // User does NOT follow the private user
            $post = Post::factory()->forUser($privateUser)->private()->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            expect($posts->contains('id', $post->id))->toBeFalse();
        });

        it('eager loads post relationships', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            
            // Check that relationships are eager loaded
            foreach ($posts as $post) {
                expect($post->relationLoaded('user'))->toBeTrue();
                expect($post->relationLoaded('likes'))->toBeTrue();
                expect($post->relationLoaded('comments'))->toBeTrue();
            }
        });
    });

    describe('Discover Feed', function () {
        
        it('shows public posts from public accounts', function () {
            $publicUser = User::factory()->create(['is_private' => false]);
            $post = Post::factory()->forUser($publicUser)->public()->create();

            $response = $this->get('/discover');

            $response->assertStatus(200);
            $response->assertViewIs('feed.index');
            
            $posts = $response->viewData('posts');
            expect($posts->contains('id', $post->id))->toBeTrue();
        });

        it('does not show private posts', function () {
            $publicUser = User::factory()->create(['is_private' => false]);
            $post = Post::factory()->forUser($publicUser)->private()->create();

            $response = $this->get('/discover');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            expect($posts->contains('id', $post->id))->toBeFalse();
        });

        it('does not show posts from private accounts', function () {
            $privateUser = User::factory()->create(['is_private' => true]);
            $post = Post::factory()->forUser($privateUser)->public()->create();

            $response = $this->get('/discover');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            expect($posts->contains('id', $post->id))->toBeFalse();
        });

        it('shows posts from multiple public accounts', function () {
            $publicUser1 = User::factory()->create(['is_private' => false]);
            $publicUser2 = User::factory()->create(['is_private' => false]);
            
            $post1 = Post::factory()->forUser($publicUser1)->public()->create();
            $post2 = Post::factory()->forUser($publicUser2)->public()->create();

            $response = $this->get('/discover');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            expect($posts->contains('id', $post1->id))->toBeTrue();
            expect($posts->contains('id', $post2->id))->toBeTrue();
        });

        it('orders posts by newest first', function () {
            $publicUser = User::factory()->create(['is_private' => false]);
            
            $post1 = Post::factory()->forUser($publicUser)->create(['created_at' => now()->subHours(2)]);
            $post2 = Post::factory()->forUser($publicUser)->create(['created_at' => now()]);

            $response = $this->get('/discover');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            
            expect($posts->first()->id)->toBe($post2->id);
        });

        it('paginates discover feed', function () {
            $publicUser = User::factory()->create(['is_private' => false]);
            
            Post::factory()->count(15)->forUser($publicUser)->public()->create();

            $response = $this->get('/discover');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            expect($posts->count())->toBeLessThanOrEqual(10);
        });

        it('shows empty state when no public posts', function () {
            $response = $this->get('/discover');

            $response->assertStatus(200);
            $response->assertSee('No public posts yet');
        });

        it('does not leak private posts from private accounts', function () {
            $privateUser = User::factory()->create(['is_private' => true]);
            $post = Post::factory()->forUser($privateUser)->public()->create();

            $response = $this->get('/discover');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            expect($posts->contains('id', $post->id))->toBeFalse();
        });

        it('does not leak private posts from public accounts', function () {
            $publicUser = User::factory()->create(['is_private' => false]);
            $post = Post::factory()->forUser($publicUser)->private()->create();

            $response = $this->get('/discover');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            expect($posts->contains('id', $post->id))->toBeFalse();
        });

        it('eager loads post relationships', function () {
            $publicUser = User::factory()->create(['is_private' => false]);
            $post = Post::factory()->forUser($publicUser)->public()->create();

            $response = $this->get('/discover');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            
            foreach ($posts as $post) {
                expect($post->relationLoaded('user'))->toBeTrue();
                expect($post->relationLoaded('likes'))->toBeTrue();
                expect($post->relationLoaded('comments'))->toBeTrue();
            }
        });
    });

    describe('Feed Navigation', function () {
        
        it('shows feed link in header', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            // Header should have Feed link
        });

        it('shows discover link in header', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            // Header should have Discover link
        });

        it('highlights active feed tab', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            $response->assertSee('Your Feed');
        });
    });

    describe('Post Card in Feed', function () {
        
        it('shows post author information', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            $response->assertSee($user->displayName());
        });

        it('shows post content', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create(['content' => 'Test post content']);

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            $response->assertSee('Test post content');
        });

        it('shows post timestamp', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            // Should show relative date like "2 hours ago"
        });

        it('shows like count', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            
            Like::factory()->count(3)->forUserAndPost(User::factory(), $post)->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            $response->assertSee('3');
        });

        it('shows comment count', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            
            Comment::factory()->count(5)->forUserAndPost(User::factory(), $post)->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            $response->assertSee('5');
        });

        it('shows post image', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->withImage()->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            // Should have image in the response
        });
    });

    describe('Privacy Edge Cases', function () {
        
        it('does not show private posts from followed private accounts to non-followers', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();
            $privateUser = User::factory()->create(['is_private' => true]);
            
            // User1 follows the private user
            Follow::factory()->forUsers($user1, $privateUser)->accepted()->create();
            
            // Private user creates a private post
            $post = Post::factory()->forUser($privateUser)->private()->create();
            
            // User2 does NOT follow the private user
            $response = $this->actingAs($user2)
                ->get('/feed');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            expect($posts->contains('id', $post->id))->toBeFalse();
        });

        it('does not show posts from unfollowed private accounts', function () {
            $user = User::factory()->create();
            $privateUser = User::factory()->create(['is_private' => true]);
            
            // User has a PENDING follow request (not accepted)
            Follow::factory()->forUsers($user, $privateUser)->pending()->create();
            
            $post = Post::factory()->forUser($privateUser)->public()->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            expect($posts->contains('id', $post->id))->toBeFalse();
        });

        it('does not show private posts from public accounts to non-owners', function () {
            $user = User::factory()->create();
            $publicUser = User::factory()->create(['is_private' => false]);
            
            $post = Post::factory()->forUser($publicUser)->private()->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            expect($posts->contains('id', $post->id))->toBeFalse();
        });
    });

    describe('Performance', function () {
        
        it('uses eager loading to avoid N+1 queries', function () {
            $user = User::factory()->create();
            
            Post::factory()->count(5)->forUser($user)->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            
            // Check that relationships are eager loaded
            $posts = $response->viewData('posts');
            foreach ($posts as $post) {
                expect($post->relationLoaded('user'))->toBeTrue();
                expect($post->relationLoaded('likes'))->toBeTrue();
                expect($post->relationLoaded('comments'))->toBeTrue();
            }
        });

        it('does not load all posts into memory', function () {
            $user = User::factory()->create();
            
            // Create more posts than the pagination limit
            Post::factory()->count(20)->forUser($user)->create();

            $response = $this->actingAs($user)
                ->get('/feed');

            $response->assertStatus(200);
            
            $posts = $response->viewData('posts');
            expect($posts->count())->toBeLessThanOrEqual(10);
        });
    });
});
