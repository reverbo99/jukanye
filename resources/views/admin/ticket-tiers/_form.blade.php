@php($tier = $tier ?? null)
@php($includesValue = old('includes'); if ($includesValue === null && $tier && is_array($tier->includes)) { $includesValue = implode("\n", $tier->includes); } $includesValue = $includesValue ?? '';)
<div class="form-grid two">
<div><label>Name (EN)</label><input type="text" name="name_en" value="{{ old('name_en', $tier->name_en ?? '') }}" required></div>
<div><label>Name (SW)</label><input type="text" name="name_sw" value="{{ old('name_sw', $tier->name_sw ?? '') }}" required></div>
</div>
<div class="form-grid two">
<div><label>Slug (optional)</label><input type="text" name="slug" value="{{ old('slug', $tier->slug ?? '') }}"></div>
<div><label>Price</label><input type="number" name="price" min="0" value="{{ old('price', $tier->price ?? 0) }}" required></div>
</div>
<div class="form-grid two">
<div><label>Currency</label><input type="text" name="currency" value="{{ old('currency', $tier->currency ?? 'TZS') }}"></div>
<div><label>Status</label><select name="status" required>@foreach(['draft'=>'Draft','published'=>'Published'] as $v=>$l)<option value="{{ $v }}" @selected(old('status', $tier->status ?? 'draft')===$v)>{{ $l }}</option>@endforeach</select></div>
</div>
<div class="form-grid two">
<div><label>Description (EN)</label><textarea name="description_en">{{ old('description_en', $tier->description_en ?? '') }}</textarea></div>
<div><label>Description (SW)</label><textarea name="description_sw">{{ old('description_sw', $tier->description_sw ?? '') }}</textarea></div>
</div>
<div class="form-grid two">
<div><label>Includes (one per line)</label><textarea name="includes">{{ $includesValue }}</textarea></div>
<div><label>Sort order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $tier->sort_order ?? 0) }}"></div>
</div>