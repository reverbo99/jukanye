<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MapPlace;
use App\Services\DeepLTranslateService;
use App\Support\Bilingual;
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

    public function store(Request $request, DeepLTranslateService $translator): RedirectResponse
    {
        MapPlace::create($this->validated($request, $translator));

        return redirect()->route('admin.map-places.index')->with('success', 'Map place created.');
    }

    public function edit(MapPlace $mapPlace): View
    {
        return view('admin.map-places.edit', ['place' => $mapPlace]);
    }

    public function update(Request $request, MapPlace $mapPlace, DeepLTranslateService $translator): RedirectResponse
    {
        $mapPlace->update($this->validated($request, $translator));

        return redirect()->route('admin.map-places.index')->with('success', 'Map place updated.');
    }

    public function destroy(MapPlace $mapPlace): RedirectResponse
    {
        $mapPlace->delete();

        return redirect()->route('admin.map-places.index')->with('success', 'Map place deleted.');
    }

    private function validated(Request $request, DeepLTranslateService $translator): array
    {
        $data = $request->validate(array_merge(
            Bilingual::pairRules('name'),
            Bilingual::pairRules('description', ['string'], false),
            [
                'lat' => ['nullable', 'numeric'],
                'lng' => ['nullable', 'numeric'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
                'status' => ['required', Rule::in(['draft', 'published'])],
            ]
        ));
        $data = $translator->fillMissingPairs($data, [
            ['name_sw', 'name_en'],
            ['description_sw', 'description_en'],
        ]);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
