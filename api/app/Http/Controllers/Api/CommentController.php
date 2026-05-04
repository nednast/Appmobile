<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(string $postId)
    {
        Post::findOrFail($postId);

        return response()->json(
            Comment::with('user')
                ->where('post_id', $postId)
                ->latest()
                ->get()
        );
    }

    public function store(Request $request, string $postId)
    {
        $user = $request->user();
        Post::findOrFail($postId);

        $validated = $request->validate(['body' => 'required|string|max:1000']);

        $comment = Comment::create([
            'post_id' => $postId,
            'user_id' => $user->id,
            'body'    => $validated['body'],
        ]);
        $comment->load('user');

        return response()->json($comment, 201);
    }

    public function destroy(Request $request, string $postId, string $commentId)
    {
        $user    = $request->user();
        $comment = Comment::where('post_id', $postId)->findOrFail($commentId);

        if ($comment->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Commentaire supprimé']);
    }
}
