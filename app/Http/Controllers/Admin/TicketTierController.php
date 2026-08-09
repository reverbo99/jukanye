<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketTier;
use App\Services\DeepLTranslateService;
use App\Support\Bilingual;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketTierController extends Controller
{
    public function index(): View
    {
        $tiers = TicketTier::query()->orderBy('sort_order')->latest()->paginate(20);

        return view('admin.ticket-tiers.index', compact('tiers'));
    }

    public function create(): View
    {
        return view('admin.ticket-tiers.create');
    }

    public function store(Request $request, DeepLTranslateService $translator): RedirectResponse
    {
        $data = $this->validated($request, $translator);
        $data['slug'] = $data['slug'] ?: TicketTier::makeSlug($data['name_en'] ?: $data['name_sw']);
        $data['includes'] = $this->parseIncludes($request->input('includes'));
        TicketTier::create($data);

        return redirect()->route('admin.ticket-tiers.index')->with('success', 'Ticket tier created.');
    }

    public function edit(TicketTier $ticketTier): View
    {
        return view('admin.ticket-tiers.edit', ['tier' => $ticketTier]);
    }

    public function update(Request $request, TicketTier $ticketTier, DeepLTranslateService $translator): RedirectResponse
    {
        $data = $this->validated($request, $translator, $ticketTier->id);
        $data['slug'] = $data['slug'] ?: $ticketTier->slug;
        $data['includes'] = $this->parseIncludes($request->input('includes'));
        $ticketTier->update($data);

        return redirect()->route('admin.ticket-tiers.index')->with('success', 'Ticket tier updated.');
    }

    public function destroy(TicketTier $ticketTier): RedirectResponse
    {
        $ticketTier->delete();

        return redirect()->route('admin.ticket-tiers.index')->with('success', 'Ticket tier deleted.');
    }

    private function validated(Request $request, DeepLTranslateService $translator, ?int $ignoreId = null): array
    {
        $data = $request->validate(array_merge(
            Bilingual::pairRules('name'),
            Bilingual::pairRules('description', ['string'], false),
            [
                'slug' => ['nullable', 'string', 'max:255', Rule::unique('ticket_tiers', 'slug')->ignore($ignoreId)],
                'price' => ['required', 'integer', 'min:0'],
                'currency' => ['nullable', 'string', 'max:10'],
                'includes' => ['nullable', 'string'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
                'status' => ['required', Rule::in(['draft', 'published'])],
            ]
        ));
        $data = $translator->fillMissingPairs($data, [
            ['name_sw', 'name_en'],
            ['description_sw', 'description_en'],
        ]);
        $data['currency'] = $data['currency'] ?? 'TZS';
        $data['sort_order'] = $data['sort_order'] ?? 0;
        unset($data['includes']);

        return $data;
    }

    private function parseIncludes(?string $raw): ?array
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        return collect(preg_split('/\r\n|\r|\n/', $raw) ?: [])
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
