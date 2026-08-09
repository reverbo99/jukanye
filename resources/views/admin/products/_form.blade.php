@php($product = $product ?? null)
@include('admin.partials.translation-hint')
<div class="form-grid two">
<div><label>Name (EN) @if($writeLocale === 'en')*@else<span class="muted">optional / auto</span>@endif</label><input type="text" name="name_en" value="{{ old('name_en', $product->name_en ?? '') }}" @if($writeLocale === 'en') required @endif @if($writeLocale !== 'en') placeholder="Auto if empty" @endif></div>
<div><label>Name (SW) @if($writeLocale === 'sw')*@else<span class="muted">optional / auto</span>@endif</label><input type="text" name="name_sw" value="{{ old('name_sw', $product->name_sw ?? '') }}" @if($writeLocale === 'sw') required @endif @if($writeLocale !== 'sw') placeholder="Auto if empty" @endif></div>
</div>
<div class="form-grid two">
<div><label>Price</label><input type="number" name="price" min="0" value="{{ old('price', $product->price ?? 0) }}" required></div>
<div><label>Currency</label><input type="text" name="currency" value="{{ old('currency', $product->currency ?? 'TZS') }}"></div>
</div>
<div class="form-grid two">
<div><label>Tagline (EN)</label><input type="text" name="tagline_en" value="{{ old('tagline_en', $product->tagline_en ?? '') }}"></div>
<div><label>Tagline (SW)</label><input type="text" name="tagline_sw" value="{{ old('tagline_sw', $product->tagline_sw ?? '') }}"></div>
</div>
<div class="form-grid two">
<div><label>Description (EN)</label><textarea name="description_en">{{ old('description_en', $product->description_en ?? '') }}</textarea></div>
<div><label>Description (SW)</label><textarea name="description_sw">{{ old('description_sw', $product->description_sw ?? '') }}</textarea></div>
</div>
<div class="form-grid two">
<div><label>Status</label><select name="status" required>@foreach(['draft'=>'Draft','published'=>'Published'] as $v=>$l)<option value="{{ $v }}" @selected(old('status', $product->status ?? 'draft')===$v)>{{ $l }}</option>@endforeach</select></div>
<div><label>Sort order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $product->sort_order ?? 0) }}"></div>
</div>
<div><label>Image</label><input type="file" name="image" accept="image/*">@if(!empty($product?->image))<p class="muted">Current: <a href="{{ asset('storage/'.$product->image) }}" target="_blank">view</a></p>@endif</div>
