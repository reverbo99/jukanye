<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\StoresPublicImages;
use App\Http\Controllers\Controller;
use App\Models\Tour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TourController extends Controller
{
    use StoresPublicImages;

    public function index(): View
    {
        $tours = Tour::query()->orderBy('sort_order')->latest()->paginate(20);

        return view('admin.tours.index', compact('tours'));
    }

    public function create(): View
    {
        return view('admin.tours.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $data['image'] = $this->storePublicImage($request->file('image'), 'tours');
        }
        Tour::create($data);

        return redirect()->route('admin.tours.index')->with('success', 'Tour created.');
    }

    public function edit(Tour $tour): View
    {
        return view('admin.tours.edit', compact('tour'));
    }

    public function update(Request $request, Tour $tour): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $this->deletePublicImage($tour->image);
            $data['image'] = $this->storePublicImage($request->file('image'), 'tours');
        }
        $tour->update($data);

        return redirect()->route('admin.tours.index')->with('success', 'Tour updated.');
    }

    public function destroy(Tour $tour): RedirectResponse
    {
        $this->deletePublicImage($tour->image);
        $tour->delete();

        return redirect()->route('admin.tours.index')->with('success', 'Tour deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_sw' => ['required', 'string', 'max:255'],
            'from_price' => ['required', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'duration_en' => ['nullable', 'string', 'max:100'],
            'duration_sw' => ['nullable', 'string', 'max:100'],
            'description_en' => ['nullable', 'string'],
            'description_sw' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);
        $data['currency'] = $data['currency'] ?? 'TZS';
        $data['sort_order'] = $data['sort_order'] ?? 0;
        unset($data['image']);

        return $data;
    }
}
