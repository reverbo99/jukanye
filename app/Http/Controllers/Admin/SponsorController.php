<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\StoresPublicImages;
use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SponsorController extends Controller
{
    use StoresPublicImages;

    public function index(): View
    {
        $sponsors = Sponsor::query()->orderBy('sort_order')->latest()->paginate(20);

        return view('admin.sponsors.index', compact('sponsors'));
    }

    public function create(): View
    {
        return view('admin.sponsors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('logo')) {
            $data['logo'] = $this->storePublicImage($request->file('logo'), 'sponsors');
        }
        Sponsor::create($data);

        return redirect()->route('admin.sponsors.index')->with('success', 'Sponsor created.');
    }

    public function edit(Sponsor $sponsor): View
    {
        return view('admin.sponsors.edit', compact('sponsor'));
    }

    public function update(Request $request, Sponsor $sponsor): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('logo')) {
            $this->deletePublicImage($sponsor->logo);
            $data['logo'] = $this->storePublicImage($request->file('logo'), 'sponsors');
        }
        $sponsor->update($data);

        return redirect()->route('admin.sponsors.index')->with('success', 'Sponsor updated.');
    }

    public function destroy(Sponsor $sponsor): RedirectResponse
    {
        $this->deletePublicImage($sponsor->logo);
        $sponsor->delete();

        return redirect()->route('admin.sponsors.index')->with('success', 'Sponsor deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],
            'tier' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'logo' => ['nullable', 'image', 'max:4096'],
        ]);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        unset($data['logo']);

        return $data;
    }
}