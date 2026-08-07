@php($sponsor = $sponsor ?? null)
<div class="form-grid two">
<div><label>Name</label><input type="text" name="name" value="{{ old('name', $sponsor->name ?? '') }}" required></div>
<div><label>Tier</label><input type="text" name="tier" value="{{ old('tier', $sponsor->tier ?? '') }}"></div>
</div>
<div class="form-grid two">
<div><label>URL</label><input type="text" name="url" value="{{ old('url', $sponsor->url ?? '') }}"></div>
<div><label>Sort order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $sponsor->sort_order ?? 0) }}"></div>
</div>
<div class="form-grid two">
<div><label>Status</label><select name="status" required>@foreach(['draft'=>'Draft','published'=>'Published'] as $v=>$l)<option value="{{ $v }}" @selected(old('status', $sponsor->status ?? 'draft')===$v)>{{ $l }}</option>@endforeach</select></div>
<div><label>Logo</label><input type="file" name="logo" accept="image/*">@if(!empty($sponsor?->logo))<p class="muted">Current: <a href="{{ asset('storage/'.$sponsor->logo) }}" target="_blank">view</a></p>@endif</div>
</div>