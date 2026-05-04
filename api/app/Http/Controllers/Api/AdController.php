<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdController extends Controller
{
    /**
     * GET /api/ads?lat=...&lng=...
     * Liste toutes les annonces triées par distance croissante.
     * Si lat/lng absents, retourne toutes les annonces sans tri.
     */
    public function index(Request $request)
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        if ($lat !== null && $lng !== null) {
            $lat = (float) $lat;
            $lng = (float) $lng;
            $ads = Ad::with('user')
                ->select('ads.*', DB::raw("
                    (6371 * acos(
                        LEAST(1.0, GREATEST(-1.0,
                            cos(radians($lat)) * cos(radians(lat))
                            * cos(radians(lng) - radians($lng))
                            + sin(radians($lat)) * sin(radians(lat))
                        ))
                    )) AS distance
                "))
                ->orderBy('distance', 'asc')
                ->get();
        } else {
            $ads = Ad::with('user')->latest()->get();
        }

        return response()->json($ads);
    }

    /**
     * GET /api/ads/{id}
     */
    public function show(string $id)
    {
        return response()->json(Ad::with('user')->findOrFail($id));
    }

    /**
     * POST /api/user/ads
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'image'       => 'nullable|image|max:2048',
            'city'        => 'required|string|max:255',
            'lat'         => 'required|numeric',
            'lng'         => 'required|numeric',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('ads', 'public');
        }

        $ad = $user->ads()->create($validated);
        $ad->load('user');

        return response()->json($ad, 201);
    }

    /**
     * POST /api/user/ads/{id}  (supporte multipart/form-data)
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $ad = Ad::findOrFail($id);

        if ($ad->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
            'city'        => 'nullable|string|max:255',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('ads', 'public');
        }

        $ad->update($validated);
        $ad->load('user');

        return response()->json($ad);
    }

    /**
     * DELETE /api/user/ads/{id}
     */
    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $ad = Ad::findOrFail($id);

        if ($ad->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $ad->delete();

        return response()->json(['message' => 'Annonce supprimée']);
    }

    /**
     * GET /api/user/ads
     * Annonces du user connecté
     */
    public function indexUser(Request $request)
    {
        $user = $request->user();

        return response()->json(
            Ad::with('user')
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(10)
        );
    }
}