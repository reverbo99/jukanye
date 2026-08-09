@php($place = $place ?? null)
@include('admin.partials.translation-hint')
<div class="form-grid two">
<div><label>Name (EN) @if($writeLocale === 'en')*@else<span class="muted">optional / auto</span>@endif</label><input type="text" name="name_en" value="{{ old('name_en', $place->name_en ?? '') }}" @if($writeLocale === 'en') required @endif @if($writeLocale !== 'en') placeholder="Auto if empty" @endif></div>
<div><label>Name (SW) @if($writeLocale === 'sw')*@else<span class="muted">optional / auto</span>@endif</label><input type="text" name="name_sw" value="{{ old('name_sw', $place->name_sw ?? '') }}" @if($writeLocale === 'sw') required @endif @if($writeLocale !== 'sw') placeholder="Auto if empty" @endif></div>
</div>
<div class="form-grid two">
<div><label>Latitude</label><input type="text" name="lat" value="{{ old('lat', $place->lat ?? '') }}"></div>
<div><label>Longitude</label><input type="text" name="lng" value="{{ old('lng', $place->lng ?? '') }}"></div>
</div>
<div class="form-grid two">
<div><label>Description (EN)</label><textarea name="description_en">{{ old('description_en', $place->description_en ?? '') }}</textarea></div>
<div><label>Description (SW)</label><textarea name="description_sw">{{ old('description_sw', $place->description_sw ?? '') }}</textarea></div>
</div>
<div class="form-grid two">
<div><label>Status</label><select name="status" required>@foreach(['draft'=>'Draft','published'=>'Published'] as $v=>$l)<option value="{{ $v }}" @selected(old('status', $place->status ?? 'draft')===$v)>{{ $l }}</option>@endforeach</select></div>
<div><label>Sort order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $place->sort_order ?? 0) }}"></div>
</div>
