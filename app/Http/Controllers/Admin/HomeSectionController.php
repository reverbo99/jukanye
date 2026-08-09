<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use App\Services\DeepLTranslateService;
use App\Support\Bilingual;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HomeSectionController extends Controller
{
    public function index(): View
    {
        $sections = HomeSection::query()->orderBy('sort_order')->latest()->paginate(30);

        return view('admin.home-sections.index', compact('sections'));
    }

    public function create(): View
    {
        return view('admin.home-sections.create');
    }

    public function store(Request $request, DeepLTranslateService $translator): RedirectResponse
    {
        HomeSection::create($this->validated($request, $translator));

        return redirect()->route('admin.home-sections.index')->with('success', 'Home section created.');
    }

    public function edit(HomeSection $homeSection): View
    {
        return view('admin.home-sections.edit', ['section' => $homeSection]);
    }

    public function update(Request $request, HomeSection $homeSection, DeepLTranslateService $translator): RedirectResponse
    {
        $homeSection->update($this->validated($request, $translator));

        return redirect()->route('admin.home-sections.index')->with('success', 'Home section updated.');
    }

    public function destroy(HomeSection $homeSection): RedirectResponse
    {
        $homeSection->delete();

        return redirect()->route('admin.home-sections.index')->with('success', 'Home section deleted.');
    }

    private function validated(Request $request, DeepLTranslateService $translator): array
    {
        $data = $request->validate(array_merge(
            Bilingual::pairRules('title'),
            Bilingual::pairRules('body', ['string'], false),
            [
                'type' => ['required', Rule::in(['objective', 'activity', 'audience', 'cta'])],
                'link' => ['nullable', 'string', 'max:500'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
                'status' => ['required', Rule::in(['draft', 'published'])],
            ]
        ));
        $data = $translator->fillMissingPairs($data, [
            ['title_sw', 'title_en'],
            ['body_sw', 'body_en'],
        ]);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
