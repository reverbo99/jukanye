@php($post = $post ?? null)
<p class="muted" style="margin:0 0 1rem;">Andika Kiswahili kwanza. Kiingereza kitatafsiriwa kiotomatiki kwa LibreTranslate ukiacha EN tupu.</p>
<div class="form-grid two">
    <div>
        <label for="title_sw">Title (SW) *</label>
        <input id="title_sw" type="text" name="title_sw" value="{{ old('title_sw', $post->title_sw ?? '') }}" required>
    </div>
    <div>
        <label for="title_en">Title (EN) <span class="muted">optional / auto</span></label>
        <input id="title_en" type="text" name="title_en" value="{{ old('title_en', $post->title_en ?? '') }}" placeholder="Auto from SW if empty">
    </div>
</div>
<div class="form-grid two">
    <div>
        <label for="slug">Slug (optional)</label>
        <input id="slug" type="text" name="slug" value="{{ old('slug', $post->slug ?? '') }}">
    </div>
    <div>
        <label for="status">Status</label>
        <select id="status" name="status" required>
            @foreach (['draft' => 'Draft', 'published' => 'Published'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $post->status ?? 'draft') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-grid two">
    <div>
        <label for="excerpt_sw">Excerpt (SW)</label>
        <textarea id="excerpt_sw" name="excerpt_sw">{{ old('excerpt_sw', $post->excerpt_sw ?? '') }}</textarea>
    </div>
    <div>
        <label for="excerpt_en">Excerpt (EN) <span class="muted">optional / auto</span></label>
        <textarea id="excerpt_en" name="excerpt_en" placeholder="Auto from SW if empty">{{ old('excerpt_en', $post->excerpt_en ?? '') }}</textarea>
    </div>
</div>
<div class="form-grid two">
    <div>
        <label for="body_sw">Body (SW)</label>
        <textarea id="body_sw" name="body_sw" style="min-height:180px;">{{ old('body_sw', $post->body_sw ?? '') }}</textarea>
    </div>
    <div>
        <label for="body_en">Body (EN) <span class="muted">optional / auto</span></label>
        <textarea id="body_en" name="body_en" style="min-height:180px;" placeholder="Auto from SW if empty">{{ old('body_en', $post->body_en ?? '') }}</textarea>
    </div>
</div>
<div class="form-grid two">
    <div>
        <label for="cover_image">Cover image</label>
        <input id="cover_image" type="file" name="cover_image" accept="image/*">
        @if (!empty($post?->cover_image))
            <p class="muted" style="margin-top:0.5rem;">Current: <a href="{{ asset('storage/'.$post->cover_image) }}" target="_blank">view</a></p>
        @endif
    </div>
    <div>
        <label for="published_at">Published at</label>
        <input id="published_at" type="datetime-local" name="published_at"
               value="{{ old('published_at', isset($post->published_at) ? $post->published_at->format('Y-m-d\TH:i') : '') }}">
    </div>
</div>
