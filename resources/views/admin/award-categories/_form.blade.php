@php($category = $category ?? null)
@include('admin.partials.translation-hint')
<div class="form-grid two">
    <div>
        <label for="name_sw">Name (SW) @if($writeLocale === 'sw')*@else<span class="muted">optional / auto</span>@endif</label>
        <input id="name_sw" type="text" name="name_sw" value="{{ old('name_sw', $category->name_sw ?? '') }}" @if($writeLocale === 'sw') required @endif @if($writeLocale !== 'sw') placeholder="Auto if empty" @endif>
    </div>
    <div>
        <label for="name_en">Name (EN) @if($writeLocale === 'en')*@else<span class="muted">optional / auto</span>@endif</label>
        <input id="name_en" type="text" name="name_en" value="{{ old('name_en', $category->name_en ?? '') }}" @if($writeLocale === 'en') required @endif @if($writeLocale !== 'en') placeholder="Auto if empty" @endif>
    </div>
</div>
<div class="form-grid two">
    <div>
        <label for="slug">Slug (optional)</label>
        <input id="slug" type="text" name="slug" value="{{ old('slug', $category->slug ?? '') }}">
    </div>
    <div>
        <label for="sort_order">Sort order</label>
        <input id="sort_order" type="number" name="sort_order" min="0" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
    </div>
</div>
<div class="form-grid two">
    <div>
        <label for="description_sw">Description (SW)</label>
        <textarea id="description_sw" name="description_sw">{{ old('description_sw', $category->description_sw ?? '') }}</textarea>
    </div>
    <div>
        <label for="description_en">Description (EN) <span class="muted">optional / auto</span></label>
        <textarea id="description_en" name="description_en" placeholder="Auto if empty">{{ old('description_en', $category->description_en ?? '') }}</textarea>
    </div>
</div>
