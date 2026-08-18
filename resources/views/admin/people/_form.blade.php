@php
    $person = $person ?? null;
    $types = $types ?? \App\Models\Person::types();
    $linksValue = old('links');
    if ($linksValue === null && $person && is_array($person->links)) {
        $linksValue = collect($person->links)->map(function ($link) {
            if (is_string($link)) {
                return $link;
            }
            if (is_array($link)) {
                return $link['url'] ?? '';
            }

            return '';
        })->filter()->implode("\n");
    }
    $linksValue = $linksValue ?? '';
@endphp
@include('admin.partials.translation-hint')
<div class="form-grid two">
<div><label>Type</label><select name="type" required>@foreach($types as $v=>$l)<option value="{{ $v }}" @selected(old('type', $person->type ?? ($defaultType ?? 'speaker'))===$v)>{{ $l }}</option>@endforeach</select></div>
<div><label>Status</label><select name="status" required>@foreach(['published'=>'Published','draft'=>'Draft'] as $v=>$l)<option value="{{ $v }}" @selected(old('status', $person->status ?? 'published')===$v)>{{ $l }}</option>@endforeach</select></div>
</div>
<div class="form-grid two">
<div><label>Name</label><input type="text" name="name" value="{{ old('name', $person->name ?? '') }}" required></div>
<div><label>Sort order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $person->sort_order ?? 0) }}"></div>
</div>
<div class="form-grid two">
<div><label>Subtitle (EN)</label><input type="text" name="subtitle_en" value="{{ old('subtitle_en', $person->subtitle_en ?? '') }}"></div>
<div><label>Subtitle (SW)</label><input type="text" name="subtitle_sw" value="{{ old('subtitle_sw', $person->subtitle_sw ?? '') }}"></div>
</div>
<div class="form-grid two">
<div><label>Bio (EN)</label><textarea name="bio_en">{{ old('bio_en', $person->bio_en ?? '') }}</textarea></div>
<div><label>Bio (SW)</label><textarea name="bio_sw">{{ old('bio_sw', $person->bio_sw ?? '') }}</textarea></div>
</div>
<div class="form-grid two">
<div><label>Photo</label><input type="file" name="photo" accept="image/*">@if($person && $person->photo)<p class="muted">Current: <a href="{{ asset('storage/'.$person->photo) }}" target="_blank">view</a></p>@endif</div>
<div><label>Links (one URL per line)</label><textarea name="links">{{ $linksValue }}</textarea></div>
</div>