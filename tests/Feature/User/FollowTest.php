<?php

use App\Models\Follow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Follow System', function () {
    
    describe('Follow Creation', function () {
        
        it('can follow a public user', function () {
            $follower = User::factory()->create();
            $following = User::factory()->create(['is_private' => false]);

            $response = $this->actingAs($follower)
                ->post('/follow/' . $following->username);

            $response->assertRedirect();
            
            $this->assertDatabaseHas('follows', [
                'follower_id' => $follower->id,
                'following_id' => $following->id,
                'status' => 'accepted',
            ]);
        });

        it('creates pending follow for private user', function () {
            $follower = User::factory()->create();
            $following = User::factory()->create(['is_private' => true]);

            $response = $this->actingAs($follower)
                ->post('/follow/' . $following->username);

            $response->assertRedirect();
            
            $this->assertDatabaseHas('follows', [
                'follower_id' => $follower->id,
                'following_id' => $following->id,
                'status' => 'pending',
            ]);
        });

        it('prevents following oneself', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->post('/follow/' . $user->username);

            $response->assertRedirect();
            $response->assertSessionHas('error', 'You cannot follow yourself.');
            
            $this->assertDatabaseMissing('follows', [
                'follower_id' => $user->id,
                'following_id' => $user->id,
            ]);
        });

        it('prevents duplicate follow for public user', function () {
            $follower = User::factory()->create();
            $following = User::factory()->create(['is_private' => false]);

            // First follow
            $this->actingAs($follower)
                ->post('/follow/' . $following->username);

            // Second follow attempt
            $response = $this->actingAs($follower)
                ->post('/follow/' . $following->username);

            $response->assertRedirect();
            $response->assertSessionHas('error', 'You are already following this user.');
            
            // Should still have only one follow record
            $this->assertDatabaseCount('follows', 1);
        });

        it('prevents duplicate follow request for private user', function () {
            $follower = User::factory()->create();
            $following = User::factory()->create(['is_private' => true]);

            // First follow request
            $this->actingAs($follower)
                ->post('/follow/' . $following->username);

            // Second follow request attempt
            $response = $this->actingAs($follower)
                ->post('/follow/' . $following->username);

            $response->assertRedirect();
            $response->assertSessionHas('error', 'You already have a pending follow request for this user.');
            
            // Should still have only one follow record
            $this->assertDatabaseCount('follows', 1);
        });

        it('cannot follow when not authenticated', function () {
            $user = User::factory()->create();

            $response = $this->post('/follow/' . $user->username);

            $response->assertRedirect('/login');
        });
    });

    describe('Unfollow', function () {
        
        it('can unfollow a user', function () {
            $follower = User::factory()->create();
            $following = User::factory()->create();
            
            // First, create a follow
            Follow::factory()->forUsers($follower, $following)->accepted()->create();

            $response = $this->actingAs($follower)
                ->delete('/follow/' . $following->username);

            $response->assertRedirect();
            $response->assertSessionHas('success', "You have unfollowed {$following->displayName()}.");
            
            $this->assertDatabaseMissing('follows', [
                'follower_id' => $follower->id,
                'following_id' => $following->id,
            ]);
        });

        it('can cancel pending follow request', function () {
            $follower = User::factory()->create();
            $following = User::factory()->create(['is_private' => true]);
            
            // Create a pending follow
            Follow::factory()->forUsers($follower, $following)->pending()->create();

            $response = $this->actingAs($follower)
                ->delete('/follow/' . $following->username);

            $response->assertRedirect();
            
            $this->assertDatabaseMissing('follows', [
                'follower_id' => $follower->id,
                'following_id' => $following->id,
            ]);
        });

        it('prevents unfollowing oneself', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->delete('/follow/' . $user->username);

            $response->assertRedirect();
            $response->assertSessionHas('error', 'You cannot unfollow yourself.');
        });

        it('prevents unfollowing user not following', function () {
            $follower = User::factory()->create();
            $following = User::factory()->create();

            $response = $this->actingAs($follower)
                ->delete('/follow/' . $following->username);

            $response->assertRedirect();
            $response->assertSessionHas('error', "You are not following {$following->displayName()}.");
        });

        it('cannot unfollow when not authenticated', function () {
            $user = User::factory()->create();

            $response = $this->delete('/follow/' . $user->username);

            $response->assertRedirect('/login');
        });
    });

    describe('Follow Request Management', function () {
        
        it('can accept follow request', function () {
            $profileOwner = User::factory()->create(['is_private' => true]);
            $follower = User::factory()->create();
            
            // Create pending follow request
            Follow::factory()->forUsers($follower, $profileOwner)->pending()->create();

            $response = $this->actingAs($profileOwner)
                ->post('/follow/' . $follower->username . '/accept');

            $response->assertRedirect();
            $response->assertSessionHas('success', "You accepted {$follower->displayName()}'s follow request.");
            
            $follow = Follow::where('follower_id', $follower->id)
                ->where('following_id', $profileOwner->id)
                ->first();
            
            expect($follow->status)->toBe('accepted');
        });

        it('can reject follow request', function () {
            $profileOwner = User::factory()->create(['is_private' => true]);
            $follower = User::factory()->create();
            
            // Create pending follow request
            Follow::factory()->forUsers($follower, $profileOwner)->pending()->create();

            $response = $this->actingAs($profileOwner)
                ->post('/follow/' . $follower->username . '/reject');

            $response->assertRedirect();
            $response->assertSessionHas('success', "You rejected {$follower->displayName()}'s follow request.");
            
            $this->assertDatabaseMissing('follows', [
                'follower_id' => $follower->id,
                'following_id' => $profileOwner->id,
            ]);
        });

        it('cannot accept non-existent follow request', function () {
            $profileOwner = User::factory()->create(['is_private' => true]);
            $follower = User::factory()->create();

            $response = $this->actingAs($profileOwner)
                ->post('/follow/' . $follower->username . '/accept');

            $response->assertRedirect();
            $response->assertSessionHas('error', 'No pending follow request found.');
        });

        it('cannot accept follow request for non-private account', function () {
            // For public accounts, follow requests are auto-accepted
            // So there should be no pending requests
            $profileOwner = User::factory()->create(['is_private' => false]);
            $follower = User::factory()->create();

            $response = $this->actingAs($profileOwner)
                ->post('/follow/' . $follower->username . '/accept');

            $response->assertRedirect();
            $response->assertSessionHas('error', 'No pending follow request found.');
        });

        it('cannot accept own follow request', function () {
            $user = User::factory()->create(['is_private' => true]);

            $response = $this->actingAs($user)
                ->post('/follow/' . $user->username . '/accept');

            $response->assertRedirect();
            $response->assertSessionHas('error', 'No pending follow request found.');
        });
    });

    describe('Followers List', function () {
        
        it('can view followers of public user', function () {
            $user = User::factory()->create(['is_private' => false]);
            
            $response = $this->get('/@' . $user->username . '/followers');

            $response->assertStatus(200);
            $response->assertViewIs('users.followers');
            $response->assertViewHas('user', $user);
        });

        it('can view followers of private user when owner', function () {
            $user = User::factory()->create(['is_private' => true]);

            $response = $this->actingAs($user)
                ->get('/@' . $user->username . '/followers');

            $response->assertStatus(200);
        });

        it('can view followers of private user when accepted follower', function () {
            $profileOwner = User::factory()->create(['is_private' => true]);
            $follower = User::factory()->create();
            
            // Create accepted follow
            Follow::factory()->forUsers($follower, $profileOwner)->accepted()->create();

            $response = $this->actingAs($follower)
                ->get('/@' . $profileOwner->username . '/followers');

            $response->assertStatus(200);
        });

        it('cannot view followers of private user when not follower', function () {
            $profileOwner = User::factory()->create(['is_private' => true]);
            $viewer = User::factory()->create();

            $response = $this->actingAs($viewer)
                ->get('/@' . $profileOwner->username . '/followers');

            $response->assertForbidden();
        });

        it('shows empty state when no followers', function () {
            $user = User::factory()->create();

            $response = $this->get('/@' . $user->username . '/followers');

            $response->assertStatus(200);
            $response->assertSee('No followers yet');
        });
    });

    describe('Following List', function () {
        
        it('can view following of public user', function () {
            $user = User::factory()->create(['is_private' => false]);

            $response = $this->get('/@' . $user->username . '/following');

            $response->assertStatus(200);
            $response->assertViewIs('users.following');
            $response->assertViewHas('user', $user);
        });

        it('can view following of private user when owner', function () {
            $user = User::factory()->create(['is_private' => true]);

            $response = $this->actingAs($user)
                ->get('/@' . $user->username . '/following');

            $response->assertStatus(200);
        });

        it('cannot view following of private user when not owner', function () {
            $profileOwner = User::factory()->create(['is_private' => true]);
            $viewer = User::factory()->create();

            $response = $this->actingAs($viewer)
                ->get('/@' . $profileOwner->username . '/following');

            $response->assertForbidden();
        });

        it('shows empty state when not following anyone', function () {
            $user = User::factory()->create();

            $response = $this->get('/@' . $user->username . '/following');

            $response->assertStatus(200);
            $response->assertSee('Not following anyone');
        });
    });

    describe('User Model Relationships', function () {
        
        it('has following relationship', function () {
            $user = User::factory()->create();
            $following = User::factory()->create();
            
            Follow::factory()->forUsers($user, $following)->accepted()->create();

            expect($user->following()->count())->toBe(1);
        });

        it('has followers relationship', function () {
            $user = User::factory()->create();
            $follower = User::factory()->create();
            
            Follow::factory()->forUsers($follower, $user)->accepted()->create();

            expect($user->followers()->count())->toBe(1);
        });

        it('has followingCount method', function () {
            $user = User::factory()->create();
            
            Follow::factory()->count(3)->forUsers($user, User::factory())->accepted()->create();

            expect($user->followingCount())->toBe(3);
        });

        it('has followerCount method', function () {
            $user = User::factory()->create();
            
            Follow::factory()->count(5)->forUsers(User::factory(), $user)->accepted()->create();

            expect($user->followerCount())->toBe(5);
        });

        it('has isFollowing method', function () {
            $user = User::factory()->create();
            $following = User::factory()->create();
            
            expect($user->isFollowing($following))->toBeFalse();
            
            Follow::factory()->forUsers($user, $following)->accepted()->create();
            
            expect($user->isFollowing($following))->toBeTrue();
        });

        it('has isFollowedBy method', function () {
            $user = User::factory()->create();
            $follower = User::factory()->create();
            
            expect($user->isFollowedBy($follower))->toBeFalse();
            
            Follow::factory()->forUsers($follower, $user)->accepted()->create();
            
            expect($user->isFollowedBy($follower))->toBeTrue();
        });

        it('has hasPendingFollowRequestFor method', function () {
            $user = User::factory()->create();
            $following = User::factory()->create(['is_private' => true]);
            
            expect($user->hasPendingFollowRequestFor($following))->toBeFalse();
            
            Follow::factory()->forUsers($user, $following)->pending()->create();
            
            expect($user->hasPendingFollowRequestFor($following))->toBeTrue();
        });

        it('has hasPendingRequestFrom method', function () {
            $user = User::factory()->create(['is_private' => true]);
            $follower = User::factory()->create();
            
            expect($user->hasPendingRequestFrom($follower))->toBeFalse();
            
            Follow::factory()->forUsers($follower, $user)->pending()->create();
            
            expect($user->hasPendingRequestFrom($follower))->toBeTrue();
        });

        it('isFollowing returns false for self', function () {
            $user = User::factory()->create();

            expect($user->isFollowing($user))->toBeFalse();
        });

        it('isFollowedBy returns false for self', function () {
            $user = User::factory()->create();

            expect($user->isFollowedBy($user))->toBeFalse();
        });

        it('canView returns true for public profiles', function () {
            $viewer = User::factory()->create();
            $profile = User::factory()->create(['is_private' => false]);

            expect($viewer->canView($profile))->toBeTrue();
        });

        it('canView returns true for own private profile', function () {
            $user = User::factory()->create(['is_private' => true]);

            expect($user->canView($user))->toBeTrue();
        });

        it('canView returns true for accepted followers of private profile', function () {
            $profile = User::factory()->create(['is_private' => true]);
            $follower = User::factory()->create();
            
            Follow::factory()->forUsers($follower, $profile)->accepted()->create();

            expect($follower->canView($profile))->toBeTrue();
        });

        it('canView returns false for non-followers of private profile', function () {
            $profile = User::factory()->create(['is_private' => true]);
            $viewer = User::factory()->create();

            expect($viewer->canView($profile))->toBeFalse();
        });
    });

    describe('Follow Model', function () {
        
        it('has follower relationship', function () {
            $follow = Follow::factory()->create();

            expect($follow->follower)->not->toBeNull();
        });

        it('has following relationship', function () {
            $follow = Follow::factory()->create();

            expect($follow->following)->not->toBeNull();
        });

        it('has isAccepted method', function () {
            $accepted = Follow::factory()->accepted()->create();
            $pending = Follow::factory()->pending()->create();

            expect($accepted->isAccepted())->toBeTrue();
            expect($pending->isAccepted())->toBeFalse();
        });

        it('has isPending method', function () {
            $accepted = Follow::factory()->accepted()->create();
            $pending = Follow::factory()->pending()->create();

            expect($pending->isPending())->toBeTrue();
            expect($accepted->isPending())->toBeFalse();
        });

        it('has accept method', function () {
            $follow = Follow::factory()->pending()->create();

            $follow->accept();

            expect($follow->isAccepted())->toBeTrue();
        });

        it('has reject method', function () {
            $follow = Follow::factory()->pending()->create();
            $followId = $follow->id;

            $follow->reject();

            $this->assertDatabaseMissing('follows', ['id' => $followId]);
        });

        it('has scopes', function () {
            Follow::factory()->accepted()->create();
            Follow::factory()->pending()->create();

            expect(Follow::accepted()->count())->toBe(1);
            expect(Follow::pending()->count())->toBe(1);
        });
    });

    describe('Database Constraints', function () {
        
        it('prevents duplicate follow records', function () {
            $follower = User::factory()->create();
            $following = User::factory()->create();
            
            // First follow
            Follow::factory()->forUsers($follower, $following)->create();

            // Try to create duplicate
            $this->expectException(\Illuminate\Database\QueryException::class);
            Follow::factory()->forUsers($follower, $following)->create();
        });

        it('cascades delete on user deletion', function () {
            $follower = User::factory()->create();
            $following = User::factory()->create();
            
            Follow::factory()->forUsers($follower, $following)->create();

            $following->delete();

            $this->assertDatabaseMissing('follows', [
                'follower_id' => $follower->id,
                'following_id' => $following->id,
            ]);
        });
    });

    describe('Profile Page Integration', function () {
        
        it('shows follow button on profile page', function () {
            $user = User::factory()->create();
            $viewer = User::factory()->create();

            $response = $this->actingAs($viewer)
                ->get('/@' . $user->username);

            $response->assertStatus(200);
            $response->assertSee('Follow');
        });

        it('shows unfollow button when already following', function () {
            $user = User::factory()->create();
            $viewer = User::factory()->create();
            
            Follow::factory()->forUsers($viewer, $user)->accepted()->create();

            $response = $this->actingAs($viewer)
                ->get('/@' . $user->username);

            $response->assertStatus(200);
            $response->assertSee('Unfollow');
        });

        it('shows requested button for pending follow request', function () {
            $user = User::factory()->create(['is_private' => true]);
            $viewer = User::factory()->create();
            
            Follow::factory()->forUsers($viewer, $user)->pending()->create();

            $response = $this->actingAs($viewer)
                ->get('/@' . $user->username);

            $response->assertStatus(200);
            $response->assertSee('Requested');
        });

        it('shows follower and following counts', function () {
            $user = User::factory()->create();
            
            $response = $this->get('/@' . $user->username);

            $response->assertStatus(200);
            $response->assertSee('Followers');
            $response->assertSee('Following');
        });

        it('does not show follow button on own profile', function () {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                ->get('/@' . $user->username);

            $response->assertStatus(200);
            $response->assertDontSee('Follow');
            $response->assertDontSee('Unfollow');
            $response->assertDontSee('Requested');
        });
    });
});
