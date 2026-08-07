@php($member = $member ?? null)
<div class="form-grid two">
<div><label>Name</label><input type="text" name="name" value="{{ old('name', $member->name ?? '') }}" required></div>
<div><label>Status</label><select name="status" required>@foreach(['draft'=>'Draft','published'=>'Published'] as $v=>$l)<option value="{{ $v }}" @selected(old('status', $member->status ?? 'draft')===$v)>{{ $l }}</option>@endforeach</select></div>
</div>
<div class="form-grid two">
<div><label>Role (EN)</label><input type="text" name="role_en" value="{{ old('role_en', $member->role_en ?? '') }}"></div>
<div><label>Role (SW)</label><input type="text" name="role_sw" value="{{ old('role_sw', $member->role_sw ?? '') }}"></div>
</div>
<div class="form-grid two">
<div><label>Bio (EN)</label><textarea name="bio_en">{{ old('bio_en', $member->bio_en ?? '') }}</textarea></div>
<div><label>Bio (SW)</label><textarea name="bio_sw">{{ old('bio_sw', $member->bio_sw ?? '') }}</textarea></div>
</div>
<div class="form-grid two">
<div><label>Photo</label><input type="file" name="photo" accept="image/*">@if(!empty($member?->photo))<p class="muted">Current: <a href="{{ asset('storage/'.$member->photo) }}" target="_blank">view</a></p>@endif</div>
<div><label>Sort order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $member->sort_order ?? 0) }}"></div>
</div>