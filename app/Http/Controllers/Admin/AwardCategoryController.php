<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAwardCategoryRequest;
use App\Http\Requests\Admin\UpdateAwardCategoryRequest;
use App\Models\AwardCategory;
use App\Services\DeepLTranslateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AwardCategoryController extends Controller
{
    public function index(): View
    {
        $categories = AwardCategory::query()->orderBy('sort_order')->orderBy('name_en')->paginate(20);

        return view('admin.award-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.award-categories.create');
    }

    public function store(StoreAwardCategoryRequest $request, DeepLTranslateService $translator): RedirectResponse
    {
        $data = $translator->fillMissingPairs($request->validated(), [
            ['name_sw', 'name_en'],
            ['description_sw', 'description_en'],
        ]);
        $data['slug'] = $data['slug'] ?: AwardCategory::makeSlug($data['name_en'] ?: $data['name_sw']);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        AwardCategory::create($data);

        return redirect()->route('admin.award-categories.index')->with('success', 'Category created.');
    }

    public function edit(AwardCategory $awardCategory): View
    {
        return view('admin.award-categories.edit', ['category' => $awardCategory]);
    }

    public function update(UpdateAwardCategoryRequest $request, AwardCategory $awardCategory, DeepLTranslateService $translator): RedirectResponse
    {
        $data = $translator->fillMissingPairs($request->validated(), [
            ['name_sw', 'name_en'],
            ['description_sw', 'description_en'],
        ]);
        $data['slug'] = $data['slug'] ?: $awardCategory->slug;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $awardCategory->update($data);

        return redirect()->route('admin.award-categories.index')->with('success', 'Category updated.');
    }

    public function destroy(AwardCategory $awardCategory): RedirectResponse
    {
        $awardCategory->delete();

        return redirect()->route('admin.award-categories.index')->with('success', 'Category deleted.');
    }
}
