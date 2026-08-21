<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supermarket;
use Illuminate\Http\JsonResponse;

class SupermarketController extends Controller
{
    public function index(): JsonResponse
    {
        $supermarkets = Supermarket::query()
            ->where('is_active', true)
            ->withCount(['flyers' => fn ($query) => $query->available()])
            ->orderBy('name')
            ->get()
            ->map(fn (Supermarket $market) => [
                'id' => $market->id,
                'name' => $market->name,
                'slug' => $market->slug,
                'description' => $market->description,
                'color' => $market->color,
                'flyers_count' => $market->flyers_count,
            ]);

        return response()->json(['data' => $supermarkets]);
    }

    public function show(Supermarket $supermarket): JsonResponse
    {
        abort_unless($supermarket->is_active, 404);

        $forwardedProto = request()->header('X-Forwarded-Proto');
        $scheme = $forwardedProto ? trim(explode(',', $forwardedProto)[0]) : request()->getScheme();
        $publicBaseUrl = $scheme.'://'.request()->getHost();

        $flyers = $supermarket->flyers()->available()->latest('starts_at')->get()->map(fn ($flyer) => [
            'id' => $flyer->id,
            'title' => $flyer->title,
            'starts_at' => $flyer->starts_at?->toDateString(),
            'ends_at' => $flyer->ends_at?->toDateString(),
            'pdf_url' => $publicBaseUrl.route('flyers.view', $flyer, false),
        ]);

        return response()->json([
            'data' => [
                'id' => $supermarket->id,
                'name' => $supermarket->name,
                'slug' => $supermarket->slug,
                'description' => $supermarket->description,
                'color' => $supermarket->color,
                'flyers' => $flyers,
            ],
        ]);
    }
}
