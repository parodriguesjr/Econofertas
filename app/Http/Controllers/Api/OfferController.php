<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q'));
        $offers = Offer::with('supermarket:id,name,slug,color')->where('is_active', true)
            ->when($query !== '', fn ($builder) => $builder->where('name', 'like', "%{$query}%"))
            ->orderByRaw('price is null, price asc')->limit(100)->get();
        $comparisons = Offer::query()->where('is_active', true)->whereNotNull('match_key')->whereNotNull('price')
            ->selectRaw('match_key, count(distinct supermarket_id) as stores, min(price) as lowest_price')
            ->groupBy('match_key')->havingRaw('count(distinct supermarket_id) > 1')->get()->keyBy('match_key');

        $data = $offers->map(function (Offer $offer) use ($comparisons) {
            $comparison = $comparisons->get($offer->match_key);

            return [
                'id' => $offer->id, 'type' => $offer->type, 'name' => $offer->name,
                'brand' => $offer->brand,
                'price' => $offer->price === null ? null : (float) $offer->price,
                'original_price' => $offer->original_price === null ? null : (float) $offer->original_price,
                'image_url' => $offer->image_url, 'source_url' => $offer->source_url,
                'city' => $offer->city, 'supermarket' => $offer->supermarket,
                'compared_stores' => $comparison ? (int) $comparison->stores : 0,
                'is_cheapest' => $comparison && (float) $offer->price === (float) $comparison->lowest_price,
            ];
        });

        return response()->json(['data' => $data]);
    }
}
