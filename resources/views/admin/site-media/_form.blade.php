@php($item = $item ?? null)
@include('admin.partials.translation-hint')

<div class="form-grid two">
    <div>
        <label>Placement *</label>
        <select name="slot" required>
            @foreach($slots as $value => $label)
                <option value="{{ $value }}" @selected(old('slot', $item->slot ?? $presetSlot ?? 'hero_slider') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <p class="muted" style="font-size:.85rem;margin-top:.35rem">
            Hero slider appears on app Home &amp; Splash. Featured videos show as a YouTube row on Home.
        </p>
    </div>
    <div>
        <label>Media type *</label>
        <select name="kind" id="jk-media-kind" required>
            @foreach(['image' => 'Image upload', 'youtube' => 'YouTube video URL'] as $value => $label)
                <option value="{{ $value }}" @selected(old('kind', $item->kind ?? 'image') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div id="jk-field-image">
    <label>Image @if(($item?->kind ?? old('kind', 'image')) === 'image' && ! $item)<span class="muted">*</span>@endif</label>
    <input type="file" name="image" accept="image/*">
    @if(!empty($item?->image))
        <p class="muted">Current: <a href="{{ asset('storage/'.$item->image) }}" target="_blank">view</a></p>
    @endif
</div>

<div id="jk-field-youtube" style="display:none">
    <label>YouTube URL *</label>
    <input type="url" name="youtube_url" value="{{ old('youtube_url', $item->youtube_url ?? '') }}" placeholder="https://www.youtube.com/watch?v=... or youtu.be/...">
    <p class="muted" style="font-size:.85rem;margin-top:.35rem">Supports watch, embed, youtu.be, and Shorts links.</p>
    @if(!empty($item?->youtube_url) && $item->youtubeThumbnail())
        <p><img src="{{ $item->youtubeThumbnail() }}" alt="YouTube preview" style="max-width:240px;border-radius:8px;margin-top:.5rem"></p>
    @endif
</div>

<div class="form-grid two">
    <div><label>Title (EN)</label><input type="text" name="title_en" value="{{ old('title_en', $item->title_en ?? '') }}"></div>
    <div><label>Title (SW)</label><input type="text" name="title_sw" value="{{ old('title_sw', $item->title_sw ?? '') }}"></div>
</div>
<div class="form-grid two">
    <div><label>Caption (EN)</label><textarea name="caption_en" rows="2">{{ old('caption_en', $item->caption_en ?? '') }}</textarea></div>
    <div><label>Caption (SW)</label><textarea name="caption_sw" rows="2">{{ old('caption_sw', $item->caption_sw ?? '') }}</textarea></div>
</div>
<div><label>Link (optional)</label><input type="url" name="link" value="{{ old('link', $item->link ?? '') }}" placeholder="https://..."></div>
<div class="form-grid two">
    <div>
        <label>Status</label>
        <select name="status" required>
            @foreach(['published' => 'Published', 'draft' => 'Draft'] as $v => $l)
                <option value="{{ $v }}" @selected(old('status', $item->status ?? 'published') === $v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div><label>Sort order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $item->sort_order ?? 0) }}"></div>
</div>

<script>
(function () {
    var kind = document.getElementById('jk-media-kind');
    var imageField = document.getElementById('jk-field-image');
    var youtubeField = document.getElementById('jk-field-youtube');
    if (!kind || !imageField || !youtubeField) return;
    function sync() {
        var isYoutube = kind.value === 'youtube';
        imageField.style.display = isYoutube ? 'none' : 'block';
        youtubeField.style.display = isYoutube ? 'block' : 'none';
    }
    kind.addEventListener('change', sync);
    sync();
})();
</script>
