<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\StoresPublicImages;
use App\Http\Controllers\Controller;
use App\Models\SiteMediaItem;
use App\Services\DeepLTranslateService;
use App\Support\Bilingual;
use App\Support\YoutubeUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SiteMediaItemController extends Controller
{
    use StoresPublicImages;

    public function index(Request $request): View
    {
        $slot = $request->string('slot')->toString();
        $query = SiteMediaItem::query()->orderBy('slot')->orderBy('sort_order')->latest();
        if ($slot !== '') {
            $query->where('slot', $slot);
        }
        $items = $query->paginate(30)->withQueryString();

        return view('admin.site-media.index', [
            'items' => $items,
            'slots' => SiteMediaItem::SLOTS,
            'currentSlot' => $slot,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.site-media.create', [
            'slots' => SiteMediaItem::SLOTS,
            'presetSlot' => $request->string('slot')->toString(),
        ]);
    }

    public function store(Request $request, DeepLTranslateService $translator): RedirectResponse
    {
        $data = $this->validated($request, $translator, null);
        if ($request->hasFile('image')) {
            $data['image'] = $this->storePublicImage($request->file('image'), 'site-media');
        }
        SiteMediaItem::create($data);

        return redirect()
            ->route('admin.site-media.index', ['slot' => $data['slot']])
            ->with('success', 'Media item created.');
    }

    public function edit(SiteMediaItem $siteMediaItem): View
    {
        return view('admin.site-media.edit', [
            'item' => $siteMediaItem,
            'slots' => SiteMediaItem::SLOTS,
        ]);
    }

    public function update(Request $request, SiteMediaItem $siteMediaItem, DeepLTranslateService $translator): RedirectResponse
    {
        $data = $this->validated($request, $translator, $siteMediaItem);
        if ($request->hasFile('image')) {
            $this->deletePublicImage($siteMediaItem->image);
            $data['image'] = $this->storePublicImage($request->file('image'), 'site-media');
        } elseif ($data['kind'] === SiteMediaItem::KIND_YOUTUBE) {
            $this->deletePublicImage($siteMediaItem->image);
            $data['image'] = null;
        }
        $siteMediaItem->update($data);

        return redirect()
            ->route('admin.site-media.index', ['slot' => $siteMediaItem->slot])
            ->with('success', 'Media item updated.');
    }

    public function destroy(SiteMediaItem $siteMediaItem): RedirectResponse
    {
        $slot = $siteMediaItem->slot;
        $this->deletePublicImage($siteMediaItem->image);
        $siteMediaItem->delete();

        return redirect()
            ->route('admin.site-media.index', ['slot' => $slot])
            ->with('success', 'Media item deleted.');
    }

    private function validated(Request $request, DeepLTranslateService $translator, ?SiteMediaItem $existing): array
    {
        $kind = $request->input('kind', $existing?->kind ?? SiteMediaItem::KIND_IMAGE);

        $data = $request->validate(array_merge(
            Bilingual::pairRules('title', ['string', 'max:255'], false),
            Bilingual::pairRules('caption', ['string', 'max:500'], false),
            [
                'slot' => ['required', Rule::in(array_keys(SiteMediaItem::SLOTS))],
                'kind' => ['required', Rule::in([SiteMediaItem::KIND_IMAGE, SiteMediaItem::KIND_YOUTUBE])],
                'youtube_url' => [
                    Rule::requiredIf($kind === SiteMediaItem::KIND_YOUTUBE),
                    'nullable',
                    'string',
                    'max:500',
                    function (string $attribute, mixed $value, \Closure $fail) use ($kind) {
                        if ($kind === SiteMediaItem::KIND_YOUTUBE && ! YoutubeUrl::isValid(is_string($value) ? $value : null)) {
                            $fail('Enter a valid YouTube URL (watch, youtu.be, embed, or shorts).');
                        }
                    },
                ],
                'link' => ['nullable', 'string', 'max:500'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
                'status' => ['required', Rule::in(['draft', 'published'])],
                'image' => [
                    Rule::requiredIf($kind === SiteMediaItem::KIND_IMAGE && $existing === null),
                    'nullable',
                    'image',
                    'max:5120',
                ],
            ]
        ));

        $data = $translator->fillMissingPairs($data, [
            ['title_sw', 'title_en'],
            ['caption_sw', 'caption_en'],
        ]);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($kind === SiteMediaItem::KIND_YOUTUBE) {
            $data['youtube_url'] = $data['youtube_url'] ?? null;
        } else {
            $data['youtube_url'] = null;
        }

        unset($data['image']);

        return $data;
    }
}
