@php($category = $category ?? null)
<p class="muted" style="margin:0 0 1rem;">Andika Kiswahili kwanza. Kiingereza kitatafsiriwa kiotomatiki ukiacha EN tupu.</p>
<div class="form-grid two">
    <div>
        <label for="name_sw">Name (SW) *</label>
        <input id="name_sw" type="text" name="name_sw" value="{{ old('name_sw', $category->name_sw ?? '') }}" required>
    </div>
    <div>
        <label for="name_en">Name (EN) <span class="muted">optional / auto</span></label>
        <input id="name_en" type="text" name="name_en" value="{{ old('name_en', $category->name_en ?? '') }}" placeholder="Auto from SW if empty">
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
        <textarea id="description_en" name="description_en" placeholder="Auto from SW if empty">{{ old('description_en', $category->description_en ?? '') }}</textarea>
    </div>
</div>
