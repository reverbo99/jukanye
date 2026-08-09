<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNomineeRequest;
use App\Http\Requests\Admin\UpdateNomineeRequest;
use App\Models\AwardCategory;
use App\Models\Media;
use App\Models\Nominee;
use App\Services\DeepLTranslateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class NomineeController extends Controller
{
    public function index(): View
    {
        $nominees = Nominee::query()
            ->with('category')
            ->orderBy('sort_order')
            ->latest()
            ->paginate(20);

        return view('admin.nominees.index', compact('nominees'));
    }

    public function create(): View
    {
        return view('admin.nominees.create', [
            'categories' => AwardCategory::orderBy('sort_order')->orderBy('name_en')->get(),
        ]);
    }

    public function store(StoreNomineeRequest $request, DeepLTranslateService $translator): RedirectResponse
    {
        $data = $translator->fillMissingPairs($request->validated(), [
            ['bio_sw', 'bio_en'],
        ]);
        $data['links'] = $this->parseLinks($data['links'] ?? null);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->storeImage($request->file('photo'));
        } else {
            unset($data['photo']);
        }

        Nominee::create($data);

        return redirect()->route('admin.nominees.index')->with('success', 'Nominee created.');
    }

    public function edit(Nominee $nominee): View
    {
        return view('admin.nominees.edit', [
            'nominee' => $nominee,
            'categories' => AwardCategory::orderBy('sort_order')->orderBy('name_en')->get(),
        ]);
    }

    public function update(UpdateNomineeRequest $request, Nominee $nominee, DeepLTranslateService $translator): RedirectResponse
    {
        $data = $translator->fillMissingPairs($request->validated(), [
            ['bio_sw', 'bio_en'],
        ]);
        $data['links'] = $this->parseLinks($data['links'] ?? null);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($request->hasFile('photo')) {
            if ($nominee->photo) {
                Storage::disk('public')->delete($nominee->photo);
            }
            $data['photo'] = $this->storeImage($request->file('photo'));
        } else {
            unset($data['photo']);
        }

        $nominee->update($data);

        return redirect()->route('admin.nominees.index')->with('success', 'Nominee updated.');
    }

    public function destroy(Nominee $nominee): RedirectResponse
    {
        if ($nominee->photo) {
            Storage::disk('public')->delete($nominee->photo);
        }
        $nominee->delete();

        return redirect()->route('admin.nominees.index')->with('success', 'Nominee deleted.');
    }

    private function parseLinks(?string $raw): ?array
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return collect(preg_split('/\r\n|\r|\n/', $raw) ?: [])
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    private function storeImage(UploadedFile $file): string
    {
        $path = $file->store('nominees', 'public');

        Media::create([
            'path' => $path,
            'disk' => 'public',
            'mime' => $file->getClientMimeType(),
            'alt' => $file->getClientOriginalName(),
        ]);

        return $path;
    }
}
