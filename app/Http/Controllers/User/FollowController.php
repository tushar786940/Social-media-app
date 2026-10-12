<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FollowController extends Controller
{
    /**
     * Follow a user.
     */
    public function follow(Request $request, User $user, DatabaseManager $db): RedirectResponse
    {
        $follower = $request->user();

        // Validate that user is not trying to follow themselves
        if ($follower->id === $user->id) {
            return back()->with('error', 'You cannot follow yourself.');
        }

        // Check if already following or has pending request
        $existingFollow = Follow::where('follower_id', $follower->id)
            ->where('following_id', $user->id)
            ->first();

        if ($existingFollow) {
            if ($existingFollow->status === 'pending') {
                return back()->with('error', 'You already have a pending follow request for this user.');
            }
            if ($existingFollow->status === 'accepted') {
                return back()->with('error', 'You are already following this user.');
            }
        }

        // Use transaction for data integrity
        return $db->transaction(function () use ($follower, $user) {
            // For private accounts, create pending follow request
            // For public accounts, create accepted follow directly
            $status = $user->is_private ? 'pending' : 'accepted';

            Follow::create([
                'follower_id' => $follower->id,
                'following_id' => $user->id,
                'status' => $status,
            ]);

            if ($status === 'accepted') {
                return back()->with('success', "You are now following {$user->displayName()}.");
            } else {
                return back()->with('success', "Follow request sent to {$user->displayName()}.");
            }
        });
    }

    /**
     * Unfollow a user.
     */
    public function unfollow(Request $request, User $user, DatabaseManager $db): RedirectResponse
    {
        $follower = $request->user();

        // Validate that user is not trying to unfollow themselves
        if ($follower->id === $user->id) {
            return back()->with('error', 'You cannot unfollow yourself.');
        }

        // Find and delete the follow relationship
        return $db->transaction(function () use ($follower, $user) {
            $follow = Follow::where('follower_id', $follower->id)
                ->where('following_id', $user->id)
                ->first();

            if (! $follow) {
                return back()->with('error', "You are not following {$user->displayName()}.");
            }

            $follow->delete();

            return back()->with('success', "You have unfollowed {$user->displayName()}.");
        });
    }

    /**
     * Accept a follow request.
     */
    public function accept(Request $request, User $follower, DatabaseManager $db): RedirectResponse
    {
        $user = $request->user();

        // Only the profile owner can accept follow requests
        if ($user->id !== $follower->id) {
            // Actually, the follower is the one requesting, so we need to check differently
            // The route is /follow/{follower}/accept, where follower is the user who wants to follow
            // The authenticated user is the one being followed (the profile owner)
            
            // So we need to find the follow where:
            // - following_id = $user->id (the profile owner)
            // - follower_id = $follower->id (the user who requested to follow)
        }

        // Find the follow request
        $follow = Follow::where('follower_id', $follower->id)
            ->where('following_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (! $follow) {
            return back()->with('error', 'No pending follow request found.');
        }

        return $db->transaction(function () use ($follow) {
            $follow->accept();

            return back()->with('success', "You accepted {$follower->displayName()}'s follow request.");
        });
    }

    /**
     * Reject a follow request.
     */
    public function reject(Request $request, User $follower, DatabaseManager $db): RedirectResponse
    {
        $user = $request->user();

        // Find the follow request
        $follow = Follow::where('follower_id', $follower->id)
            ->where('following_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (! $follow) {
            return back()->with('error', 'No pending follow request found.');
        }

        return $db->transaction(function () use ($follow) {
            $follow->reject();

            return back()->with('success', "You rejected {$follower->displayName()}'s follow request.");
        });
    }

    /**
     * List followers of a user.
     */
    public function followers(Request $request, User $user)
    {
        // Check if authenticated user can view this user's followers
        if ($user->is_private) {
            $this->authorize('viewPosts', $user);
        }

        $followers = $user->followers()
            ->wherePivot('status', 'accepted')
            ->withTimestamps()
            ->paginate(20);

        return view('users.followers', [
            'user' => $user,
            'followers' => $followers,
            'type' => 'followers',
        ]);
    }

    /**
     * List users that a user is following.
     */
    public function following(Request $request, User $user)
    {
        // For private accounts, only the owner can see who they're following
        if ($user->is_private && ($request->user() && $request->user()->id !== $user->id)) {
            abort(403, 'This account is private.');
        }

        $following = $user->following()
            ->wherePivot('status', 'accepted')
            ->withTimestamps()
            ->paginate(20);

        return view('users.following', [
            'user' => $user,
            'following' => $following,
            'type' => 'following',
        ]);
    }

    /**
     * Get follow status for a user.
     */
    public function status(Request $request, User $user)
    {
        $currentUser = $request->user();

        if (! $currentUser) {
            return response()->json([
                'isFollowing' => false,
                'hasPendingRequest' => false,
                'canFollow' => false,
            ]);
        }

        if ($currentUser->id === $user->id) {
            return response()->json([
                'isFollowing' => false,
                'hasPendingRequest' => false,
                'canFollow' => false,
            ]);
        }

        return response()->json([
            'isFollowing' => $currentUser->isFollowing($user),
            'hasPendingRequest' => $currentUser->hasPendingFollowRequestFor($user),
            'canFollow' => true,
            'requiresApproval' => $user->is_private,
        ]);
    }
}
