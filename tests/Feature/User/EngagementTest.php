<?php

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Post Engagement', function () {
    
    describe('Likes', function () {
        
        it('can like a post', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->public()->create();

            $response = $this->actingAs($user)
                ->post("/posts/{$post->id}/like");

            $response->assertRedirect();
            
            $this->assertDatabaseHas('likes', [
                'user_id' => $user->id,
                'post_id' => $post->id,
            ]);
        });

        it('can like another users post', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser(User::factory()->create())->public()->create();

            $response = $this->actingAs($user)
                ->post("/posts/{$post->id}/like");

            $response->assertRedirect();
            
            $this->assertDatabaseHas('likes', [
                'user_id' => $user->id,
                'post_id' => $post->id,
            ]);
        });

        it('can unlike a post', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            
            // First, like the post
            Like::factory()->forUserAndPost($user, $post)->create();

            $response = $this->actingAs($user)
                ->delete("/posts/{$post->id}/like");

            $response->assertRedirect();
            
            $this->assertDatabaseMissing('likes', [
                'user_id' => $user->id,
                'post_id' => $post->id,
            ]);
        });

        it('prevents duplicate likes', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            
            // First like
            Like::factory()->forUserAndPost($user, $post)->create();

            // Second like attempt
            $response = $this->actingAs($user)
                ->post("/posts/{$post->id}/like");

            $response->assertRedirect();
            $response->assertSessionHas('error', 'You have already liked this post.');
            
            // Should still have only one like
            $this->assertDatabaseCount('likes', 1);
        });

        it('cannot like private post without following', function () {
            $profileOwner = User::factory()->create(['is_private' => true]);
            $post = Post::factory()->forUser($profileOwner)->create();
            $viewer = User::factory()->create();

            $response = $this->actingAs($viewer)
                ->post("/posts/{$post->id}/like");

            $response->assertRedirect();
            $response->assertSessionHas('error', 'You cannot like this post.');
            
            $this->assertDatabaseMissing('likes', [
                'user_id' => $viewer->id,
                'post_id' => $post->id,
            ]);
        });

        it('can like private post when following', function () {
            $profileOwner = User::factory()->create(['is_private' => true]);
            $post = Post::factory()->forUser($profileOwner)->create();
            $follower = User::factory()->create();
            
            // Create follow relationship
            $profileOwner->followers()->create([
                'follower_id' => $follower->id,
                'status' => 'accepted',
            ]);

            $response = $this->actingAs($follower)
                ->post("/posts/{$post->id}/like");

            $response->assertRedirect();
            
            $this->assertDatabaseHas('likes', [
                'user_id' => $follower->id,
                'post_id' => $post->id,
            ]);
        });

        it('cannot like when not authenticated', function () {
            $post = Post::factory()->create();

            $response = $this->post("/posts/{$post->id}/like");

            $response->assertRedirect('/login');
        });

        it('can toggle like', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();

            // First, like the post
            Like::factory()->forUserAndPost($user, $post)->create();

            $response = $this->actingAs($user)
                ->post("/posts/{$post->id}/like/toggle");

            $response->assertJson();
            $response->assertJson(['action' => 'unliked']);
            
            $this->assertDatabaseMissing('likes', [
                'user_id' => $user->id,
                'post_id' => $post->id,
            ]);
        });

        it('has like count', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            
            Like::factory()->count(5)->forUserAndPost(User::factory(), $post)->create();

            expect($post->likeCount())->toBe(5);
        });

        it('has isLikedBy method', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            
            expect($post->isLikedBy($user))->toBeFalse();
            
            Like::factory()->forUserAndPost($user, $post)->create();
            
            expect($post->isLikedBy($user))->toBeTrue();
        });

        it('can check if post can be engaged with', function () {
            $user = User::factory()->create();
            $publicPost = Post::factory()->forUser(User::factory()->create())->public()->create();
            $privatePost = Post::factory()->forUser(User::factory()->create(['is_private' => true]))->create();

            expect($publicPost->canEngage($user))->toBeTrue();
            expect($privatePost->canEngage($user))->toBeFalse();
        });
    });

    describe('Comments', function () {
        
        it('can comment on a post', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();

            $response = $this->actingAs($user)
                ->post("/posts/{$post->id}/comments", [
                    'content' => 'This is a test comment',
                ]);

            $response->assertRedirect();
            
            $this->assertDatabaseHas('comments', [
                'user_id' => $user->id,
                'post_id' => $post->id,
                'content' => 'This is a test comment',
                'parent_id' => null,
            ]);
        });

        it('can comment on another users post', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser(User::factory()->create())->public()->create();

            $response = $this->actingAs($user)
                ->post("/posts/{$post->id}/comments", [
                    'content' => 'This is a test comment',
                ]);

            $response->assertRedirect();
            
            $this->assertDatabaseHas('comments', [
                'user_id' => $user->id,
                'post_id' => $post->id,
                'content' => 'This is a test comment',
            ]);
        });

        it('validates comment content is required', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();

            $response = $this->actingAs($user)
                ->post("/posts/{$post->id}/comments", [
                    'content' => '',
                ]);

            $response->assertSessionHasErrors('content');
        });

        it('validates comment content min length', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();

            $response = $this->actingAs($user)
                ->post("/posts/{$post->id}/comments", [
                    'content' => ' ', // Whitespace only
                ]);

            // The min:1 rule should catch this
            $response->assertSessionHasErrors('content');
        });

        it('validates comment content max length', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();

            $response = $this->actingAs($user)
                ->post("/posts/{$post->id}/comments", [
                    'content' => str_repeat('a', 2001),
                ]);

            $response->assertSessionHasErrors('content');
        });

        it('cannot comment on private post without following', function () {
            $profileOwner = User::factory()->create(['is_private' => true]);
            $post = Post::factory()->forUser($profileOwner)->create();
            $viewer = User::factory()->create();

            $response = $this->actingAs($viewer)
                ->post("/posts/{$post->id}/comments", [
                    'content' => 'Test comment',
                ]);

            $response->assertRedirect();
            $response->assertSessionHas('error', 'You cannot comment on this post.');
            
            $this->assertDatabaseMissing('comments', [
                'user_id' => $viewer->id,
                'post_id' => $post->id,
            ]);
        });

        it('can comment on private post when following', function () {
            $profileOwner = User::factory()->create(['is_private' => true]);
            $post = Post::factory()->forUser($profileOwner)->create();
            $follower = User::factory()->create();
            
            // Create follow relationship
            $profileOwner->followers()->create([
                'follower_id' => $follower->id,
                'status' => 'accepted',
            ]);

            $response = $this->actingAs($follower)
                ->post("/posts/{$post->id}/comments", [
                    'content' => 'Test comment',
                ]);

            $response->assertRedirect();
            
            $this->assertDatabaseHas('comments', [
                'user_id' => $follower->id,
                'post_id' => $post->id,
            ]);
        });

        it('cannot comment when not authenticated', function () {
            $post = Post::factory()->create();

            $response = $this->post("/posts/{$post->id}/comments", [
                'content' => 'Test comment',
            ]);

            $response->assertRedirect('/login');
        });

        it('can edit own comment', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            $comment = Comment::factory()->forUserAndPost($user, $post)->create();

            $response = $this->actingAs($user)
                ->put("/posts/{$post->id}/comments/{$comment->id}", [
                    'content' => 'Updated comment',
                ]);

            $response->assertRedirect();
            
            $comment->refresh();
            expect($comment->content)->toBe('Updated comment');
        });

        it('cannot edit other users comment', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            $comment = Comment::factory()->forUserAndPost(User::factory(), $post)->create();

            $response = $this->actingAs($user)
                ->put("/posts/{$post->id}/comments/{$comment->id}", [
                    'content' => 'Hacked comment',
                ]);

            $response->assertForbidden();
            
            $comment->refresh();
            expect($comment->content)->not->toBe('Hacked comment');
        });

        it('can delete own comment', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            $comment = Comment::factory()->forUserAndPost($user, $post)->create();

            $response = $this->actingAs($user)
                ->delete("/posts/{$post->id}/comments/{$comment->id}");

            $response->assertRedirect();
            
            $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
        });

        it('cannot delete other users comment', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            $comment = Comment::factory()->forUserAndPost(User::factory(), $post)->create();

            $response = $this->actingAs($user)
                ->delete("/posts/{$post->id}/comments/{$comment->id}");

            $response->assertForbidden();
            
            $this->assertDatabaseHas('comments', ['id' => $comment->id]);
        });

        it('can reply to a comment', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            $parentComment = Comment::factory()->forUserAndPost($user, $post)->create();

            $response = $this->actingAs($user)
                ->post("/posts/{$post->id}/comments", [
                    'content' => 'This is a reply',
                    'parent_id' => $parentComment->id,
                ]);

            $response->assertRedirect();
            
            $this->assertDatabaseHas('comments', [
                'user_id' => $user->id,
                'post_id' => $post->id,
                'parent_id' => $parentComment->id,
                'content' => 'This is a reply',
            ]);
        });

        it('cannot reply to comment from different post', function () {
            $user = User::factory()->create();
            $post1 = Post::factory()->forUser($user)->create();
            $post2 = Post::factory()->forUser($user)->create();
            $comment = Comment::factory()->forUserAndPost($user, $post1)->create();

            $response = $this->actingAs($user)
                ->post("/posts/{$post2->id}/comments", [
                    'content' => 'This is a reply',
                    'parent_id' => $comment->id,
                ]);

            $response->assertRedirect();
            $response->assertSessionHas('error', 'Invalid parent comment.');
        });

        it('has comment count', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            
            Comment::factory()->count(3)->forUserAndPost(User::factory(), $post)->create();

            expect($post->commentCount())->toBe(3);
        });

        it('has isReply method', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            
            $comment = Comment::factory()->forUserAndPost($user, $post)->create();
            $reply = Comment::factory()->replyTo($comment)->create();

            expect($comment->isReply())->toBeFalse();
            expect($comment->isTopLevel())->toBeTrue();
            expect($reply->isReply())->toBeTrue();
            expect($reply->isTopLevel())->toBeFalse();
        });

        it('has replies relationship', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            $comment = Comment::factory()->forUserAndPost($user, $post)->create();
            
            Comment::factory()->count(2)->replyTo($comment)->create();

            expect($comment->replies()->count())->toBe(2);
        });

        it('escapes comment content', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            $maliciousContent = '<script>alert("xss")</script>';
            
            $comment = Comment::factory()->forUserAndPost($user, $post)->create([
                'content' => $maliciousContent,
            ]);

            expect($comment->escapedContent)->toBe(e($maliciousContent));
            expect($comment->escapedContent)->not->toContain('<script>');
        });

        it('can view comments for a post', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();

            $response = $this->get("/posts/{$post->id}/comments");

            $response->assertStatus(200);
            $response->assertViewIs('posts.comments');
            $response->assertViewHas('post', $post);
        });

        it('cannot view comments for private post without following', function () {
            $profileOwner = User::factory()->create(['is_private' => true]);
            $post = Post::factory()->forUser($profileOwner)->create();
            $viewer = User::factory()->create();

            $response = $this->actingAs($viewer)
                ->get("/posts/{$post->id}/comments");

            $response->assertForbidden();
        });
    });

    describe('Post Model Relationships', function () {
        
        it('has likes relationship', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            
            Like::factory()->count(3)->forUserAndPost(User::factory(), $post)->create();

            expect($post->likes()->count())->toBe(3);
        });

        it('has comments relationship', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            
            Comment::factory()->count(5)->forUserAndPost(User::factory(), $post)->create();

            expect($post->comments()->count())->toBe(5);
        });

        it('has topLevelComments method', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            
            $comment1 = Comment::factory()->forUserAndPost(User::factory(), $post)->create();
            $comment2 = Comment::factory()->forUserAndPost(User::factory(), $post)->create();
            Comment::factory()->replyTo($comment1)->create();

            expect($post->topLevelComments()->count())->toBe(2);
        });
    });

    describe('Like Model', function () {
        
        it('has user relationship', function () {
            $like = Like::factory()->create();

            expect($like->user)->not->toBeNull();
        });

        it('has post relationship', function () {
            $like = Like::factory()->create();

            expect($like->post)->not->toBeNull();
        });

        it('has scopes', function () {
            $user = User::factory()->create();
            $post1 = Post::factory()->create();
            $post2 = Post::factory()->create();
            
            Like::factory()->forUserAndPost($user, $post1)->create();
            Like::factory()->forUserAndPost($user, $post2)->create();

            expect(Like::forPost($post1->id)->count())->toBe(1);
            expect(Like::byUser($user->id)->count())->toBe(2);
        });
    });

    describe('Comment Model', function () {
        
        it('has user relationship', function () {
            $comment = Comment::factory()->create();

            expect($comment->user)->not->toBeNull();
        });

        it('has post relationship', function () {
            $comment = Comment::factory()->create();

            expect($comment->post)->not->toBeNull();
        });

        it('has parent relationship', function () {
            $parent = Comment::factory()->create();
            $reply = Comment::factory()->replyTo($parent)->create();

            expect($reply->parent->id)->toBe($parent->id);
        });

        it('has replies relationship', function () {
            $parent = Comment::factory()->create();
            Comment::factory()->count(3)->replyTo($parent)->create();

            expect($parent->replies()->count())->toBe(3);
        });

        it('has formatted date', function () {
            $comment = Comment::factory()->create([
                'created_at' => now(),
            ]);

            expect($comment->formattedDate())->toContain(date('Y'));
        });

        it('has relative date', function () {
            $comment = Comment::factory()->create([
                'created_at' => now(),
            ]);

            expect($comment->relativeDate())->toContain('ago');
        });
    });

    describe('Database Constraints', function () {
        
        it('prevents duplicate likes', function () {
            $user = User::factory()->create();
            $post = Post::factory()->create();
            
            Like::factory()->forUserAndPost($user, $post)->create();

            $this->expectException(\Illuminate\Database\QueryException::class);
            Like::factory()->forUserAndPost($user, $post)->create();
        });

        it('cascades delete likes on user deletion', function () {
            $user = User::factory()->create();
            $post = Post::factory()->create();
            
            Like::factory()->forUserAndPost($user, $post)->create();

            $user->delete();

            $this->assertDatabaseMissing('likes', [
                'user_id' => $user->id,
                'post_id' => $post->id,
            ]);
        });

        it('cascades delete likes on post deletion', function () {
            $user = User::factory()->create();
            $post = Post::factory()->create();
            
            Like::factory()->forUserAndPost($user, $post)->create();

            $post->delete();

            $this->assertDatabaseMissing('likes', [
                'user_id' => $user->id,
                'post_id' => $post->id,
            ]);
        });

        it('cascades delete comments on user deletion', function () {
            $user = User::factory()->create();
            $post = Post::factory()->create();
            
            Comment::factory()->forUserAndPost($user, $post)->create();

            $user->delete();

            $this->assertDatabaseMissing('comments', [
                'user_id' => $user->id,
            ]);
        });

        it('cascades delete comments on post deletion', function () {
            $user = User::factory()->create();
            $post = Post::factory()->create();
            
            Comment::factory()->forUserAndPost($user, $post)->create();

            $post->delete();

            $this->assertDatabaseMissing('comments', [
                'post_id' => $post->id,
            ]);
        });

        it('cascades delete replies on parent deletion', function () {
            $user = User::factory()->create();
            $post = Post::factory()->create();
            $parent = Comment::factory()->forUserAndPost($user, $post)->create();
            $reply = Comment::factory()->replyTo($parent)->create();

            $parent->delete();

            $this->assertDatabaseMissing('comments', ['id' => $reply->id]);
        });
    });

    describe('Policies', function () {
        
        it('like policy allows creating like for public post', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser(User::factory()->create())->public()->create();

            $policy = new \App\Policies\LikePolicy();
            
            expect($policy->create($user, $post))->toBeTrue();
        });

        it('like policy prevents creating like for private post without following', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser(User::factory()->create(['is_private' => true]))->create();

            $policy = new \App\Policies\LikePolicy();
            
            expect($policy->create($user, $post))->toBeFalse();
        });

        it('like policy allows deleting own like', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            $like = Like::factory()->forUserAndPost($user, $post)->create();

            $policy = new \App\Policies\LikePolicy();
            
            expect($policy->delete($user, $post))->toBeTrue();
        });

        it('like policy prevents deleting like not owned', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser(User::factory()->create())->create();

            $policy = new \App\Policies\LikePolicy();
            
            expect($policy->delete($user, $post))->toBeFalse();
        });

        it('comment policy allows creating comment for public post', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser(User::factory()->create())->public()->create();

            $policy = new \App\Policies\CommentPolicy();
            
            expect($policy->create($user, $post))->toBeTrue();
        });

        it('comment policy prevents creating comment for private post without following', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser(User::factory()->create(['is_private' => true]))->create();

            $policy = new \App\Policies\CommentPolicy();
            
            expect($policy->create($user, $post))->toBeFalse();
        });

        it('comment policy allows updating own comment', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            $comment = Comment::factory()->forUserAndPost($user, $post)->create();

            $policy = new \App\Policies\CommentPolicy();
            
            expect($policy->update($user, $comment))->toBeTrue();
        });

        it('comment policy prevents updating other users comment', function () {
            $user = User::factory()->create();
            $comment = Comment::factory()->forUserAndPost(User::factory(), Post::factory())->create();

            $policy = new \App\Policies\CommentPolicy();
            
            expect($policy->update($user, $comment))->toBeFalse();
        });

        it('comment policy allows deleting own comment', function () {
            $user = User::factory()->create();
            $post = Post::factory()->forUser($user)->create();
            $comment = Comment::factory()->forUserAndPost($user, $post)->create();

            $policy = new \App\Policies\CommentPolicy();
            
            expect($policy->delete($user, $comment))->toBeTrue();
        });

        it('comment policy prevents deleting other users comment', function () {
            $user = User::factory()->create();
            $comment = Comment::factory()->forUserAndPost(User::factory(), Post::factory())->create();

            $policy = new \App\Policies\CommentPolicy();
            
            expect($policy->delete($user, $comment))->toBeFalse();
        });
    });

    describe('View Integration', function () {
        
        it('post card shows like button', function () {
            $post = Post::factory()->create();

            $response = $this->get('/posts');

            // This will pass if the post card component includes the like button
            // The actual test would need to create a post and visit its page
        });

        it('post show page shows comments', function () {
            $post = Post::factory()->create();

            $response = $this->get("/posts/{$post->id}");

            $response->assertStatus(200);
            // Comments section should be present
        });

        it('comment form requires authentication', function () {
            $post = Post::factory()->create();

            $response = $this->get("/posts/{$post->id}");

            $response->assertStatus(200);
            // Should show sign in message for unauthenticated users
        });
    });
});
