<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreScheduleItemRequest;
use App\Http\Requests\Admin\UpdateScheduleItemRequest;
use App\Models\ScheduleItem;
use App\Services\DeepLTranslateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ScheduleItemController extends Controller
{
    public function index(): View
    {
        $items = ScheduleItem::query()
            ->orderBy('starts_at')
            ->orderBy('sort_order')
            ->paginate(20);

        return view('admin.schedule.index', compact('items'));
    }

    public function create(): View
    {
        return view('admin.schedule.create');
    }

    public function store(StoreScheduleItemRequest $request, DeepLTranslateService $translator): RedirectResponse
    {
        $data = $translator->fillMissingPairs($request->validated(), [
            ['title_sw', 'title_en'],
            ['description_sw', 'description_en'],
            ['location_sw', 'location_en'],
        ]);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        ScheduleItem::create($data);

        return redirect()->route('admin.schedule.index')->with('success', 'Schedule item created.');
    }

    public function edit(ScheduleItem $scheduleItem): View
    {
        return view('admin.schedule.edit', ['item' => $scheduleItem]);
    }

    public function update(UpdateScheduleItemRequest $request, ScheduleItem $scheduleItem, DeepLTranslateService $translator): RedirectResponse
    {
        $data = $translator->fillMissingPairs($request->validated(), [
            ['title_sw', 'title_en'],
            ['description_sw', 'description_en'],
            ['location_sw', 'location_en'],
        ]);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $scheduleItem->update($data);

        return redirect()->route('admin.schedule.index')->with('success', 'Schedule item updated.');
    }

    public function destroy(ScheduleItem $scheduleItem): RedirectResponse
    {
        $scheduleItem->delete();

        return redirect()->route('admin.schedule.index')->with('success', 'Schedule item deleted.');
    }
}
