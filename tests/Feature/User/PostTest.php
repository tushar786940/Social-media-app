<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['app.maintenance.driver' => 'file']);
    Storage::fake('public');
});

describe('Posts', function () {
    
    describe('Post Creation', function () {
        
        it('can create a post with text only', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->post('/posts', [
                    'content' => 'This is a test post',
                    'visibility' => 'public',
                ]);

            $response->assertRedirect();
            
            $this->assertDatabaseHas('posts', [
                'user_id' => $user->id,
                'content' => 'This is a test post',
                'visibility' => 'public',
            ]);
        });

        it('can create a post with image', function () {
            $user = User::factory()->create();

            $file = UploadedFile::fake()->image('post.jpg');

            $response = $this->actingAs($user)
                ->post('/posts', [
                    'content' => 'Post with image',
                    'image' => $file,
                    'visibility' => 'public',
                ]);

            $response->assertRedirect();
            
            $post = Post::where('user_id', $user->id)->first();
            
            expect($post->image_path)->not->toBeNull();
            Storage::disk('public')->assertExists($post->image_path);
        });

        it('can create a private post', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->post('/posts', [
                    'content' => 'Private post',
                    'visibility' => 'private',
                ]);

            $response->assertRedirect();
            
            $this->assertDatabaseHas('posts', [
                'user_id' => $user->id,
                'visibility' => 'private',
            ]);
        });

        it('validates content is required', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->post('/posts', [
                    'content' => '',
                    'visibility' => 'public',
                ]);

            $response->assertSessionHasErrors('content');
        });

        it('validates content max length', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->post('/posts', [
                    'content' => str_repeat('a', 5001),
                    'visibility' => 'public',
                ]);

            $response->assertSessionHasErrors('content');
        });

        it('validates visibility is required', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->post('/posts', [
                    'content' => 'Test post',
                ]);

            $response->assertSessionHasErrors('visibility');
        });

        it('validates visibility is in allowed values', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->post('/posts', [
                    'content' => 'Test post',
                    'visibility' => 'invalid',
                ]);

            $response->assertSessionHasErrors('visibility');
        });

        it('validates image file type', function () {
            $user = User::factory()->create();

            $file = UploadedFile::fake()->create('document.pdf');

            $response = $this->actingAs($user)
                ->post('/posts', [
                    'content' => 'Test post',
                    'image' => $file,
                    'visibility' => 'public',
                ]);

            $response->assertSessionHasErrors('image');
        });

        it('validates image file size', function () {
            $user = User::factory()->create();

            $file = UploadedFile::fake()->image('large.jpg')->size(3000);

            $response = $this->actingAs($user)
                ->post('/posts', [
                    'content' => 'Test post',
                    'image' => $file,
                    'visibility' => 'public',
                ]);

            $response->assertSessionHasErrors('image');
        });

        it('cannot create post when not authenticated', function () {
            $response = $this->post('/posts', [
                'content' => 'Test post',
                'visibility' => 'public',
            ]);

            $response->assertRedirect('/login');
        });
    });

    describe('Post Viewing', function () {
        
        it('can view public post', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->public()->create();

            $response = $this->get('/posts/' . $post->id);

            $response->assertStatus(200);
            $response->assertViewIs('posts.show');
            $response->assertViewHas('post', $post);
        });

        it('can view own private post', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->private()->create();

            $response = $this->actingAs($user)
                ->get('/posts/' . $post->id);

            $response->assertStatus(200);
        });

        it('cannot view other users private post', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();
            $post = Post::factory()->forUser($user1)->private()->create();

            $response = $this->actingAs($user2)
                ->get('/posts/' . $post->id);

            $response->assertForbidden();
        });

        it('can view posts index', function () {
            $response = $this->get('/posts');

            $response->assertStatus(200);
            $response->assertViewIs('posts.index');
        });

        it('can view create post form when authenticated', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->get('/posts/create');

            $response->assertStatus(200);
            $response->assertViewIs('posts.create');
        });

        it('cannot view create post form when not authenticated', function () {
            $response = $this->get('/posts/create');

            $response->assertRedirect('/login');
        });
    });

    describe('Post Editing', function () {
        
        it('can edit own post', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();

            $response = $this->actingAs($user)
                ->get('/posts/' . $post->id . '/edit');

            $response->assertStatus(200);
            $response->assertViewIs('posts.edit');
            $response->assertViewHas('post', $post);
        });

        it('can update own post', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create([
                'content' => 'Original content',
            ]);

            $response = $this->actingAs($user)
                ->put('/posts/' . $post->id, [
                    'content' => 'Updated content',
                    'visibility' => 'public',
                ]);

            $response->assertRedirect();
            
            $post->refresh();
            
            expect($post->content)->toBe('Updated content');
        });

        it('can update post visibility', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->public()->create();

            $response = $this->actingAs($user)
                ->put('/posts/' . $post->id, [
                    'content' => $post->content,
                    'visibility' => 'private',
                ]);

            $response->assertRedirect();
            
            $post->refresh();
            
            expect($post->visibility)->toBe('private');
        });

        it('can replace post image', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->withImage()->create();
            
            Storage::disk('public')->put($post->image_path, 'dummy');

            $newFile = UploadedFile::fake()->image('new.jpg');

            $response = $this->actingAs($user)
                ->put('/posts/' . $post->id, [
                    'content' => $post->content,
                    'visibility' => $post->visibility,
                    'image' => $newFile,
                ]);

            $response->assertRedirect();
            
            $post->refresh();
            
            expect($post->image_path)->not->toBeNull();
            Storage::disk('public')->assertExists($post->image_path);
        });

        it('cannot edit other users post', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();
            $post = Post::factory()->forUser($user1)->create();

            $response = $this->actingAs($user2)
                ->get('/posts/' . $post->id . '/edit');

            $response->assertForbidden();
        });

        it('cannot update other users post', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();
            $post = Post::factory()->forUser($user1)->create();

            $response = $this->actingAs($user2)
                ->put('/posts/' . $post->id, [
                    'content' => 'Hacked content',
                    'visibility' => 'public',
                ]);

            $response->assertForbidden();
            
            $post->refresh();
            expect($post->content)->not->toBe('Hacked content');
        });

        it('validates update content is required', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();

            $response = $this->actingAs($user)
                ->put('/posts/' . $post->id, [
                    'content' => '',
                    'visibility' => 'public',
                ]);

            $response->assertSessionHasErrors('content');
        });
    });

    describe('Post Deletion', function () {
        
        it('can delete own post', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();

            $response = $this->actingAs($user)
                ->delete('/posts/' . $post->id);

            $response->assertRedirect();
            
            $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        });

        it('deletes image when post is deleted', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->withImage()->create();
            
            Storage::disk('public')->put($post->image_path, 'dummy');

            $response = $this->actingAs($user)
                ->delete('/posts/' . $post->id);

            $response->assertRedirect();
            
            Storage::disk('public')->assertMissing($post->image_path);
        });

        it('cannot delete other users post', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();
            $post = Post::factory()->forUser($user1)->create();

            $response = $this->actingAs($user2)
                ->delete('/posts/' . $post->id);

            $response->assertForbidden();
            
            $this->assertDatabaseHas('posts', ['id' => $post->id]);
        });

        it('cannot delete post when not authenticated', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();

            $response = $this->delete('/posts/' . $post->id);

            $response->assertRedirect('/login');
        });
    });

    describe('Image Management', function () {
        
        it('can remove post image', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->withImage()->create();
            
            Storage::disk('public')->put($post->image_path, 'dummy');

            $response = $this->actingAs($user)
                ->delete('/posts/' . $post->id . '/image');

            $response->assertRedirect();
            
            $post->refresh();
            
            expect($post->image_path)->toBeNull();
            Storage::disk('public')->assertMissing($post->image_path);
        });

        it('cannot remove image from other users post', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();
            $post = Post::factory()->forUser($user1)->withImage()->create();
            
            Storage::disk('public')->put($post->image_path, 'dummy');

            $response = $this->actingAs($user2)
                ->delete('/posts/' . $post->id . '/image');

            $response->assertForbidden();
            
            $post->refresh();
            expect($post->image_path)->not->toBeNull();
        });
    });

    describe('Post Model', function () {
        
        it('has user relationship', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();

            expect($post->user->id)->toBe($user->id);
        });

        it('has image URL accessor', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->withImage()->create();

            Storage::disk('public')->put($post->image_path, 'dummy');

            expect($post->image_url)->toContain($post->image_path);
        });

        it('has hasImage method', function () {
            $user = User::factory()->create();
            $post1 = Post::factory()->forUser($user)->create();
            $post2 = Post::factory()->forUser($user)->withImage()->create();

            expect($post1->hasImage())->toBeFalse();
            expect($post2->hasImage())->toBeTrue();
        });

        it('has formatted date', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create([
                'created_at' => now(),
            ]);

            expect($post->formattedDate())->toContain(date('Y'));
        });

        it('has relative date', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create([
                'created_at' => now(),
            ]);

            expect($post->relativeDate())->toContain('ago');
        });

        it('has visibility label', function () {
            $user = User::factory()->create();
            $post1 = Post::factory()->forUser($user)->public()->create();
            $post2 = Post::factory()->forUser($user)->private()->create();

            expect($post1->visibilityLabel())->toBe('Public');
            expect($post2->visibilityLabel())->toBe('Private');
        });

        it('has author accessors', function () {
            $user = User::factory()->create([
                'name' => 'Test User',
                'username' => 'testuser',
            ]);
            $post = Post::factory()->forUser($user)->create();

            expect($post->authorName())->toBe('Test User');
            expect($post->authorUsername())->toBe('testuser');
            expect($post->authorUrl())->toContain('/@testuser');
        });

        it('has URL methods', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();

            expect($post->url())->toContain('/posts/');
            expect($post->editUrl())->toContain('/posts/' . $post->id . '/edit');
        });
    });

    describe('User Model Relationships', function () {
        
        it('has posts relationship', function () {
            $user = User::factory()->create();
            Post::factory()->count(3)->forUser($user)->create();

            expect($user->posts()->count())->toBe(3);
        });

        it('has publicPosts method', function () {
            $user = User::factory()->create();
            Post::factory()->count(2)->forUser($user)->public()->create();
            Post::factory()->forUser($user)->private()->create();

            expect($user->publicPosts()->count())->toBe(2);
        });

        it('has postCount method', function () {
            $user = User::factory()->create();
            Post::factory()->count(5)->forUser($user)->create();

            expect($user->postCount())->toBe(5);
        });
    });

    describe('Post Scopes', function () {
        
        it('has public scope', function () {
            $user = User::factory()->create();
            Post::factory()->count(2)->forUser($user)->public()->create();
            Post::factory()->forUser($user)->private()->create();

            expect(Post::public()->count())->toBe(2);
        });

        it('has forUser scope', function () {
            $user = User::factory()->create();
            Post::factory()->count(3)->forUser($user)->create();

            expect(Post::forUser($user->id)->count())->toBe(3);
        });

        it('has newest scope', function () {
            $user = User::factory()->create();
            $post1 = Post::factory()->forUser($user)->create(['created_at' => now()->subDays(2)]);
            $post2 = Post::factory()->forUser($user)->create(['created_at' => now()->subDay()]);
            $post3 = Post::factory()->forUser($user)->create(['created_at' => now()]);

            $posts = Post::newest()->get();
            
            expect($posts->first()->id)->toBe($post3->id);
            expect($posts->last()->id)->toBe($post1->id);
        });
    });

    describe('Pagination', function () {
        
        it('paginates posts on index', function () {
            $user = User::factory()->create();
            Post::factory()->count(15)->forUser($user)->public()->create();

            $response = $this->get('/posts');

            $response->assertStatus(200);
            
            // The view should have paginated posts
        });
    });
});
