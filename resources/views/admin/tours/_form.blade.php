@php($tour = $tour ?? null)
@include('admin.partials.translation-hint')
<div class="form-grid two">
<div><label>Name (EN) @if($writeLocale === 'en')*@else<span class="muted">optional / auto</span>@endif</label><input type="text" name="name_en" value="{{ old('name_en', $tour->name_en ?? '') }}" @if($writeLocale === 'en') required @endif @if($writeLocale !== 'en') placeholder="Auto if empty" @endif></div>
<div><label>Name (SW) @if($writeLocale === 'sw')*@else<span class="muted">optional / auto</span>@endif</label><input type="text" name="name_sw" value="{{ old('name_sw', $tour->name_sw ?? '') }}" @if($writeLocale === 'sw') required @endif @if($writeLocale !== 'sw') placeholder="Auto if empty" @endif></div>
</div>
<div class="form-grid two">
<div><label>From price</label><input type="number" name="from_price" min="0" value="{{ old('from_price', $tour->from_price ?? 0) }}" required></div>
<div><label>Currency</label><input type="text" name="currency" value="{{ old('currency', $tour->currency ?? 'TZS') }}"></div>
</div>
<div class="form-grid two">
<div><label>Duration (EN)</label><input type="text" name="duration_en" value="{{ old('duration_en', $tour->duration_en ?? '') }}"></div>
<div><label>Duration (SW)</label><input type="text" name="duration_sw" value="{{ old('duration_sw', $tour->duration_sw ?? '') }}"></div>
</div>
<div class="form-grid two">
<div><label>Description (EN)</label><textarea name="description_en">{{ old('description_en', $tour->description_en ?? '') }}</textarea></div>
<div><label>Description (SW)</label><textarea name="description_sw">{{ old('description_sw', $tour->description_sw ?? '') }}</textarea></div>
</div>
<div class="form-grid two">
<div><label>Status</label><select name="status" required>@foreach(['published'=>'Published','draft'=>'Draft'] as $v=>$l)<option value="{{ $v }}" @selected(old('status', $tour->status ?? 'published')===$v)>{{ $l }}</option>@endforeach</select></div>
<div><label>Sort order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $tour->sort_order ?? 0) }}"></div>
</div>
<div><label>Image</label><input type="file" name="image" accept="image/*">@if(!empty($tour?->image))<p class="muted">Current: <a href="{{ asset('storage/'.$tour->image) }}" target="_blank">view</a></p>@endif</div>
