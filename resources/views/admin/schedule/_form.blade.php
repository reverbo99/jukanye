@php($item = $item ?? null)
<p class="muted" style="margin:0 0 1rem;">Andika Kiswahili kwanza. Kiingereza kitatafsiriwa kiotomatiki ukiacha EN tupu.</p>
<div class="form-grid two">
    <div>
        <label for="title_sw">Title (SW) *</label>
        <input id="title_sw" type="text" name="title_sw" value="{{ old('title_sw', $item->title_sw ?? '') }}" required>
    </div>
    <div>
        <label for="title_en">Title (EN) <span class="muted">optional / auto</span></label>
        <input id="title_en" type="text" name="title_en" value="{{ old('title_en', $item->title_en ?? '') }}" placeholder="Auto from SW if empty">
    </div>
</div>
<div class="form-grid two">
    <div>
        <label for="starts_at">Starts at</label>
        <input id="starts_at" type="datetime-local" name="starts_at"
               value="{{ old('starts_at', isset($item->starts_at) ? $item->starts_at->format('Y-m-d\TH:i') : '') }}" required>
    </div>
    <div>
        <label for="ends_at">Ends at</label>
        <input id="ends_at" type="datetime-local" name="ends_at"
               value="{{ old('ends_at', isset($item->ends_at) ? $item->ends_at->format('Y-m-d\TH:i') : '') }}">
    </div>
</div>
<div class="form-grid two">
    <div>
        <label for="location_sw">Location (SW)</label>
        <input id="location_sw" type="text" name="location_sw" value="{{ old('location_sw', $item->location_sw ?? '') }}">
    </div>
    <div>
        <label for="location_en">Location (EN) <span class="muted">optional / auto</span></label>
        <input id="location_en" type="text" name="location_en" value="{{ old('location_en', $item->location_en ?? '') }}" placeholder="Auto from SW if empty">
    </div>
</div>
<div class="form-grid two">
    <div>
        <label for="category">Category / track</label>
        <input id="category" type="text" name="category" value="{{ old('category', $item->category ?? '') }}">
    </div>
    <div>
        <label for="status">Status</label>
        <select id="status" name="status" required>
            @foreach (['draft' => 'Draft', 'published' => 'Published'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $item->status ?? 'draft') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-grid two">
    <div>
        <label for="description_sw">Description (SW)</label>
        <textarea id="description_sw" name="description_sw">{{ old('description_sw', $item->description_sw ?? '') }}</textarea>
    </div>
    <div>
        <label for="description_en">Description (EN) <span class="muted">optional / auto</span></label>
        <textarea id="description_en" name="description_en" placeholder="Auto from SW if empty">{{ old('description_en', $item->description_en ?? '') }}</textarea>
    </div>
</div>
<div>
    <label for="sort_order">Sort order</label>
    <input id="sort_order" type="number" name="sort_order" min="0" value="{{ old('sort_order', $item->sort_order ?? 0) }}">
</div>
