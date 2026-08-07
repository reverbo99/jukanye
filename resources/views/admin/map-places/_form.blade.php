@php($place = $place ?? null)
<div class="form-grid two">
<div><label>Name (EN)</label><input type="text" name="name_en" value="{{ old('name_en', $place->name_en ?? '') }}" required></div>
<div><label>Name (SW)</label><input type="text" name="name_sw" value="{{ old('name_sw', $place->name_sw ?? '') }}" required></div>
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