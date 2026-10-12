<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    /**
     * Display the authenticated user's feed.
     * Shows posts from the user and users they follow (with proper visibility).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Get IDs of users the authenticated user can see posts from
        $visibleUserIds = $this->getVisibleUserIds($user);

        // Query posts from these users, respecting post visibility
        $posts = Post::with([
                'user',
                'likes',
                'comments' => function ($query) {
                    $query->topLevel()->with(['user', 'replies' => function ($q) {
                        $q->with('user')->newest();
                    }])->newest();
                },
            ])
            ->whereIn('user_id', $visibleUserIds)
            ->where(function ($query) use ($user) {
                // Public posts are always visible
                $query->where('visibility', 'public');
                
                // Private posts: only show if the post owner is the authenticated user
                // or if the authenticated user follows the post owner (and owner's account allows it)
                $query->orWhere(function ($q) use ($user) {
                    $q->where('visibility', 'private')
                        ->where('user_id', $user->id);
                });
            })
            ->newest()
            ->paginate(10);

        return view('feed.index', [
            'posts' => $posts,
            'feedType' => 'home',
        ]);
    }

    /**
     * Display a public/discovery feed.
     * Shows only public posts from public accounts.
     */
    public function discover(Request $request)
    {
        // Get public posts from public accounts
        $posts = Post::with([
                'user',
                'likes',
                'comments' => function ($query) {
                    $query->topLevel()->with(['user', 'replies' => function ($q) {
                        $q->with('user')->newest();
                    }])->newest();
                },
            ])
            ->where('visibility', 'public')
            ->whereHas('user', function ($query) {
                $query->where('is_private', false);
            })
            ->newest()
            ->paginate(10);

        return view('feed.index', [
            'posts' => $posts,
            'feedType' => 'discover',
        ]);
    }

    /**
     * Get IDs of users whose posts the authenticated user can see.
     */
    protected function getVisibleUserIds(User $user): array
    {
        // User can always see their own posts
        $visibleUserIds = [$user->id];

        // Get users the authenticated user follows
        $following = $user->following()
            ->wherePivot('status', 'accepted')
            ->pluck('users.id')
            ->toArray();

        // For each followed user, check if their account is public or if we follow them
        // Since we already filtered by accepted follows, we can add all of them
        // But we need to respect their account privacy for private posts
        $visibleUserIds = array_merge($visibleUserIds, $following);

        return $visibleUserIds;
    }
}
