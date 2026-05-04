<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggle(Request $request, string $postId)
    {
        $user = $request->user();
        Post::findOrFail($postId);

        $existing = Like::where('post_id', $postId)->where('user_id', $user->id)->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            Like::create(['post_id' => $postId, 'user_id' => $user->id]);
            $liked = true;
        }

        return response()->json([
            'liked'       => $liked,
            'likes_count' => Like::where('post_id', $postId)->count(),
        ]);
    }
}
