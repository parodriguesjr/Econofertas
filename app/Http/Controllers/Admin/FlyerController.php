<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flyer;
use App\Models\Supermarket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FlyerController extends Controller
{
    public function index(): View
    {
        $flyers = Flyer::with('supermarket')->latest()->paginate(15);
        return view('admin.flyers.index', compact('flyers'));
    }

    public function create(): View
    {
        return view('admin.flyers.form', ['flyer' => new Flyer, 'supermarkets' => Supermarket::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['file_path'] = $request->file('pdf')->store('flyers');
        Flyer::create($data);
        return redirect()->route('admin.flyers.index')->with('success', 'Folheto publicado.');
    }

    public function edit(Flyer $flyer): View
    {
        return view('admin.flyers.form', ['flyer' => $flyer, 'supermarkets' => Supermarket::orderBy('name')->get()]);
    }

    public function preview(Flyer $flyer): Response
    {
        abort_unless(Storage::disk('local')->exists($flyer->file_path), 404);

        return response(Storage::disk('local')->get($flyer->file_path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="folheto.pdf"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function update(Request $request, Flyer $flyer): RedirectResponse
    {
        $data = $this->validated($request, false);
        if ($request->hasFile('pdf')) {
            Storage::disk('local')->delete($flyer->file_path);
            $data['file_path'] = $request->file('pdf')->store('flyers');
        }
        $flyer->update($data);
        return redirect()->route('admin.flyers.index')->with('success', 'Folheto atualizado.');
    }

    public function destroy(Flyer $flyer): RedirectResponse
    {
        Storage::disk('local')->delete($flyer->file_path);
        $flyer->delete();
        return back()->with('success', 'Folheto excluído.');
    }

    private function validated(Request $request, bool $pdfRequired = true): array
    {
        $data = $request->validate([
            'supermarket_id' => ['required', 'exists:supermarkets,id'],
            'title' => ['required', 'string', 'max:150'],
            'pdf' => [$pdfRequired ? 'required' : 'nullable', 'file', 'mimes:pdf', 'max:20480'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);
        unset($data['pdf']);
        $data['is_published'] = $request->boolean('is_published');
        return $data;
    }
}
