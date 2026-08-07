<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MapPlace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MapPlaceController extends Controller
{
    public function index(): View
    {
        $places = MapPlace::query()->orderBy('sort_order')->latest()->paginate(30);

        return view('admin.map-places.index', compact('places'));
    }

    public function create(): View
    {
        return view('admin.map-places.create');
    }

    public function store(Request $request): RedirectResponse
    {
        MapPlace::create($this->validated($request));

        return redirect()->route('admin.map-places.index')->with('success', 'Map place created.');
    }

    public function edit(MapPlace $mapPlace): View
    {
        return view('admin.map-places.edit', ['place' => $mapPlace]);
    }

    public function update(Request $request, MapPlace $mapPlace): RedirectResponse
    {
        $mapPlace->update($this->validated($request));

        return redirect()->route('admin.map-places.index')->with('success', 'Map place updated.');
    }

    public function destroy(MapPlace $mapPlace): RedirectResponse
    {
        $mapPlace->delete();

        return redirect()->route('admin.map-places.index')->with('success', 'Map place deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_sw' => ['required', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'description_en' => ['nullable', 'string'],
            'description_sw' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ]);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
