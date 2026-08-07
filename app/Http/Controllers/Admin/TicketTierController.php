<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketTier;
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

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: TicketTier::makeSlug($data['name_en']);
        $data['includes'] = $this->parseIncludes($request->input('includes'));
        TicketTier::create($data);

        return redirect()->route('admin.ticket-tiers.index')->with('success', 'Ticket tier created.');
    }

    public function edit(TicketTier $ticketTier): View
    {
        return view('admin.ticket-tiers.edit', ['tier' => $ticketTier]);
    }

    public function update(Request $request, TicketTier $ticketTier): RedirectResponse
    {
        $data = $this->validated($request, $ticketTier->id);
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

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('ticket_tiers', 'slug')->ignore($ignoreId)],
            'name_en' => ['required', 'string', 'max:255'],
            'name_sw' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'description_en' => ['nullable', 'string'],
            'description_sw' => ['nullable', 'string'],
            'includes' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'published'])],
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
