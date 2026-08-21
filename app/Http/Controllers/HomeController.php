<?php

namespace App\Http\Controllers;

use App\Models\Flyer;
use App\Models\Supermarket;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $supermarkets = Supermarket::where('is_active', true)
            ->withCount(['flyers' => fn ($query) => $query->available()])
            ->orderBy('name')->get();
        return view('home', compact('supermarkets'));
    }

    public function supermarket(Supermarket $supermarket): View
    {
        abort_unless($supermarket->is_active, 404);
        $flyers = $supermarket->flyers()->available()->latest('starts_at')->get();
        return view('supermarket', compact('supermarket', 'flyers'));
    }

    public function flyer(Flyer $flyer): Response
    {
        abort_unless($flyer->is_published && $flyer->supermarket->is_active && Storage::disk('local')->exists($flyer->file_path), 404);
        abort_if($flyer->starts_at?->isAfter(today()) || $flyer->ends_at?->isBefore(today()), 404);
        return response(Storage::disk('local')->get($flyer->file_path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="folheto.pdf"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
