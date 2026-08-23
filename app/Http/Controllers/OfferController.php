<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q'));
        $offers = Offer::with('supermarket')->where('is_active', true)
            ->when($query !== '', fn ($builder) => $builder->where('name', 'like', "%{$query}%"))
            ->orderByRaw('price is null, price asc')->latest('captured_at')
            ->paginate(24)->withQueryString();

        return view('offers.index', compact('offers', 'query'));
    }
}
