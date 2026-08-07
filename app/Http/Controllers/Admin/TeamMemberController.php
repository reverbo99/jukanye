<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\StoresPublicImages;
use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeamMemberController extends Controller
{
    use StoresPublicImages;

    public function index(): View
    {
        $members = TeamMember::query()->orderBy('sort_order')->latest()->paginate(20);

        return view('admin.team.index', compact('members'));
    }

    public function create(): View
    {
        return view('admin.team.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('photo')) {
            $data['photo'] = $this->storePublicImage($request->file('photo'), 'team');
        }
        TeamMember::create($data);

        return redirect()->route('admin.team.index')->with('success', 'Team member created.');
    }

    public function edit(TeamMember $team): View
    {
        return view('admin.team.edit', ['member' => $team]);
    }

    public function update(Request $request, TeamMember $team): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('photo')) {
            $this->deletePublicImage($team->photo);
            $data['photo'] = $this->storePublicImage($request->file('photo'), 'team');
        }
        $team->update($data);

        return redirect()->route('admin.team.index')->with('success', 'Team member updated.');
    }

    public function destroy(TeamMember $team): RedirectResponse
    {
        $this->deletePublicImage($team->photo);
        $team->delete();

        return redirect()->route('admin.team.index')->with('success', 'Team member deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role_en' => ['nullable', 'string', 'max:255'],
            'role_sw' => ['nullable', 'string', 'max:255'],
            'bio_en' => ['nullable', 'string'],
            'bio_sw' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        unset($data['photo']);

        return $data;
    }
}