<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         return Post::with('user')->latest()->get();

    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    $user = $request->user();

    $validated = $request->validate([
        'description' => 'required|string',
        'image'       => 'nullable|image|max:2048',
    ]);

    if ($request->hasFile('image')) {
        $validated['image'] = $request->file('image')->store('posts', 'public');
    }

    $post = $user->posts()->create($validated);
    $post->load('user');

    return response()->json($post, 201);
}
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Post::with('user')->findOrFail($id);
    }

    public function indexUser(Request $request)
    {
        $user = $request->user();
        return Post::with('user')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);
    }


    /**
     * Display the specified resource for the authenticated user.
     */
    public function showUser(Request $request, string $id)
{
    $user = $request->user();
    $post = Post::with('user')->findOrFail($id);

    if ($post->user_id !== $user->id) {
        return response()->json(['message' => 'Forbidden'], 403);
    }

    return $post;
}
    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, string $id)
{
    $user = $request->user();
    $post = Post::findOrFail($id);

    if ($post->user_id !== $user->id) {
        return response()->json(['message' => 'Forbidden'], 403);
    }

    $validated = $request->validate([
        'description' => 'nullable|string',
        'image'       => 'nullable|image|max:2048',
    ]);

    if ($request->hasFile('image')) {
        $validated['image'] = $request->file('image')->store('posts', 'public');
    }

    $post->update($validated);
    $post->load('user');

    return response()->json($post);
}


    /**
     * Remove the specified resource from storage.
     */
   public function destroy(Request $request, string $id)
{
    $user = $request->user();
    $post = Post::findOrFail($id);

    if ($post->user_id !== $user->id) {
        return response()->json(['message' => 'Forbidden'], 403);
    }

    $post->delete();

    return response()->json(['message' => 'Post supprimé']);
}
}
