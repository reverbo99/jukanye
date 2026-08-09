@php
    $nominee = $nominee ?? null;
    $linksValue = old('links');
    if ($linksValue === null && $nominee) {
        $rawLinks = $nominee->links;
        if (is_array($rawLinks)) {
            $linksValue = collect($rawLinks)->map(function ($link) {
                if (is_string($link)) {
                    return $link;
                }
                if (is_array($link)) {
                    return $link['url'] ?? '';
                }

                return '';
            })->filter()->implode("\n");
        } else {
            $linksValue = '';
        }
    }
    $linksValue = $linksValue ?? '';
@endphp
@include('admin.partials.translation-hint')
<div class="form-grid two">
    <div>
        <label for="name">Name</label>
        <input id="name" type="text" name="name" value="{{ old('name', $nominee->name ?? '') }}" required>
    </div>
    <div>
        <label for="award_category_id">Category</label>
        <select id="award_category_id" name="award_category_id" required>
            <option value="">Select…</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('award_category_id', $nominee->award_category_id ?? '') === (string) $category->id)>
                    {{ $category->name_en }}
                </option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-grid two">
    <div>
        <label for="country">Country</label>
        <input id="country" type="text" name="country" value="{{ old('country', $nominee->country ?? '') }}">
    </div>
    <div>
        <label for="status">Status</label>
        <select id="status" name="status" required>
            @foreach (['published' => 'Published', 'draft' => 'Draft'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $nominee->status ?? 'published') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-grid two">
    <div>
        <label for="bio_en">Bio (EN)</label>
        <textarea id="bio_en" name="bio_en">{{ old('bio_en', $nominee->bio_en ?? '') }}</textarea>
    </div>
    <div>
        <label for="bio_sw">Bio (SW)</label>
        <textarea id="bio_sw" name="bio_sw">{{ old('bio_sw', $nominee->bio_sw ?? '') }}</textarea>
    </div>
</div>
<div class="form-grid two">
    <div>
        <label for="photo">Photo</label>
        <input id="photo" type="file" name="photo" accept="image/*">
        @if (!empty($nominee?->photo))
            <p class="muted" style="margin-top:0.5rem;">Current: <a href="{{ asset('storage/'.$nominee->photo) }}" target="_blank">view</a></p>
        @endif
    </div>
    <div>
        <label for="sort_order">Sort order</label>
        <input id="sort_order" type="number" name="sort_order" min="0" value="{{ old('sort_order', $nominee->sort_order ?? 0) }}">
    </div>
</div>
<div>
    <label for="links">Links (one URL per line, or JSON array)</label>
    <textarea id="links" name="links">{{ $linksValue }}</textarea>
</div>
