@php($post = $post ?? null)
@include('admin.partials.translation-hint')
<div class="form-grid two">
    <div>
        <label for="title_sw">Title (SW) @if($writeLocale === 'sw')*@else<span class="muted">optional / auto</span>@endif</label>
        <input id="title_sw" type="text" name="title_sw" value="{{ old('title_sw', $post->title_sw ?? '') }}" @if($writeLocale === 'sw') required @endif @if($writeLocale !== 'sw') placeholder="Auto from {{ strtoupper($writeLocale) }} if empty" @endif>
    </div>
    <div>
        <label for="title_en">Title (EN) @if($writeLocale === 'en')*@else<span class="muted">optional / auto</span>@endif</label>
        <input id="title_en" type="text" name="title_en" value="{{ old('title_en', $post->title_en ?? '') }}" @if($writeLocale === 'en') required @endif @if($writeLocale !== 'en') placeholder="Auto from {{ strtoupper($writeLocale) }} if empty" @endif>
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
            @foreach (['published' => 'Published', 'draft' => 'Draft'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $post->status ?? 'published') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-grid two">
    <div>
        <label for="excerpt_sw">Excerpt (SW)</label>
        <textarea id="excerpt_sw" name="excerpt_sw" @if($writeLocale !== 'sw') placeholder="Auto from {{ strtoupper($writeLocale) }} if empty" @endif>{{ old('excerpt_sw', $post->excerpt_sw ?? '') }}</textarea>
    </div>
    <div>
        <label for="excerpt_en">Excerpt (EN) <span class="muted">optional / auto</span></label>
        <textarea id="excerpt_en" name="excerpt_en" @if($writeLocale !== 'en') placeholder="Auto from {{ strtoupper($writeLocale) }} if empty" @endif>{{ old('excerpt_en', $post->excerpt_en ?? '') }}</textarea>
    </div>
</div>
<div class="form-grid two">
    <div>
        <label for="body_sw">Body (SW)</label>
        <textarea id="body_sw" name="body_sw" style="min-height:180px;" @if($writeLocale !== 'sw') placeholder="Auto from {{ strtoupper($writeLocale) }} if empty" @endif>{{ old('body_sw', $post->body_sw ?? '') }}</textarea>
    </div>
    <div>
        <label for="body_en">Body (EN) <span class="muted">optional / auto</span></label>
        <textarea id="body_en" name="body_en" style="min-height:180px;" @if($writeLocale !== 'en') placeholder="Auto from {{ strtoupper($writeLocale) }} if empty" @endif>{{ old('body_en', $post->body_en ?? '') }}</textarea>
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
