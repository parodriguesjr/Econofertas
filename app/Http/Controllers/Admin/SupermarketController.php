<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supermarket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupermarketController extends Controller
{
    public function index(): View
    {
        $supermarkets = Supermarket::withCount('flyers')->orderBy('name')->get();
        return view('admin.supermarkets.index', compact('supermarkets'));
    }

    public function create(): View { return view('admin.supermarkets.form', ['supermarket' => new Supermarket]); }

    public function store(Request $request): RedirectResponse
    {
        Supermarket::create($this->validated($request));
        return redirect()->route('admin.supermarkets.index')->with('success', 'Supermercado cadastrado.');
    }

    public function edit(Supermarket $supermarket): View { return view('admin.supermarkets.form', compact('supermarket')); }

    public function update(Request $request, Supermarket $supermarket): RedirectResponse
    {
        $supermarket->update($this->validated($request, $supermarket));
        return redirect()->route('admin.supermarkets.index')->with('success', 'Supermercado atualizado.');
    }

    public function destroy(Supermarket $supermarket): RedirectResponse
    {
        $supermarket->delete();
        return back()->with('success', 'Supermercado excluído.');
    }

    private function validated(Request $request, ?Supermarket $supermarket = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'alpha_dash', 'max:140', Rule::unique('supermarkets')->ignore($supermarket)],
            'description' => ['nullable', 'string', 'max:500'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }
}
