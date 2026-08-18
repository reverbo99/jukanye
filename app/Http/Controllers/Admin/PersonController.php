<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\StoresPublicImages;
use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Services\DeepLTranslateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonController extends Controller
{
    use StoresPublicImages;

    public function index(Request $request): View
    {
        $type = $request->string('type')->toString();
        $query = Person::query()->orderBy('sort_order')->latest();
        if ($type !== '' && array_key_exists($type, Person::types())) {
            $query->where('type', $type);
        }
        $people = $query->paginate(20)->withQueryString();

        return view('admin.people.index', [
            'people' => $people,
            'type' => $type,
            'types' => Person::types(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.people.create', [
            'types' => Person::types(),
            'defaultType' => $request->string('type')->toString() ?: 'speaker',
        ]);
    }

    public function store(Request $request, DeepLTranslateService $translator): RedirectResponse
    {
        $data = $this->validated($request, $translator);
        $data['links'] = $this->parseLinks($request->input('links'));
        $photo = $request->file('photo');
        if ($photo && $photo->isValid()) {
            $data['photo'] = $this->storePublicImage($photo, 'people');
        }
        Person::create($data);

        return redirect()->route('admin.people.index', ['type' => $data['type']])->with('success', 'Person created.');
    }

    public function edit(Request $request, Person $person): View
    {
        return view('admin.people.edit', [
            'person' => $person,
            'types' => Person::types(),
            'type' => $request->string('type')->toString() ?: $person->type,
        ]);
    }

    public function update(Request $request, Person $person, DeepLTranslateService $translator): RedirectResponse
    {
        $data = $this->validated($request, $translator);
        $data['links'] = $this->parseLinks($request->input('links'));
        $photo = $request->file('photo');
        if ($photo && $photo->isValid()) {
            $this->deletePublicImage($person->photo);
            $data['photo'] = $this->storePublicImage($photo, 'people');
        }
        $person->update($data);

        $type = $request->string('type')->toString() ?: $person->type;

        return redirect()
            ->route('admin.people.index', ['type' => $type])
            ->with('success', $person->name.' updated.');
    }

    public function destroy(Person $person): RedirectResponse
    {
        $type = $person->type;
        $this->deletePublicImage($person->photo);
        $person->delete();

        return redirect()->route('admin.people.index', ['type' => $type])->with('success', 'Person deleted.');
    }

    private function validated(Request $request, DeepLTranslateService $translator): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(Person::types()))],
            'name' => ['required', 'string', 'max:255'],
            'subtitle_en' => ['nullable', 'string', 'max:255'],
            'subtitle_sw' => ['nullable', 'string', 'max:255'],
            'bio_en' => ['nullable', 'string'],
            'bio_sw' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'photo' => ['nullable', 'image', 'max:8192'],
            'links' => ['nullable', 'string'],
        ]);
        $data = $translator->fillMissingPairs($data, [
            ['subtitle_sw', 'subtitle_en'],
            ['bio_sw', 'bio_en'],
        ]);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        unset($data['photo'], $data['links']);

        return $data;
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
}
