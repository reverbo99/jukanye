@php($section = $section ?? null)
@include('admin.partials.translation-hint')
<div class="form-grid two">
<div><label>Type</label><select name="type" required>@foreach(['objective'=>'Objective','activity'=>'Activity','audience'=>'Audience','cta'=>'CTA'] as $v=>$l)<option value="{{ $v }}" @selected(old('type', $section->type ?? 'objective')===$v)>{{ $l }}</option>@endforeach</select></div>
<div><label>Status</label><select name="status" required>@foreach(['draft'=>'Draft','published'=>'Published'] as $v=>$l)<option value="{{ $v }}" @selected(old('status', $section->status ?? 'draft')===$v)>{{ $l }}</option>@endforeach</select></div>
</div>
<div class="form-grid two">
<div><label>Title (EN) @if($writeLocale === 'en')*@else<span class="muted">optional / auto</span>@endif</label><input type="text" name="title_en" value="{{ old('title_en', $section->title_en ?? '') }}" @if($writeLocale === 'en') required @endif @if($writeLocale !== 'en') placeholder="Auto if empty" @endif></div>
<div><label>Title (SW) @if($writeLocale === 'sw')*@else<span class="muted">optional / auto</span>@endif</label><input type="text" name="title_sw" value="{{ old('title_sw', $section->title_sw ?? '') }}" @if($writeLocale === 'sw') required @endif @if($writeLocale !== 'sw') placeholder="Auto if empty" @endif></div>
</div>
<div class="form-grid two">
<div><label>Body (EN)</label><textarea name="body_en">{{ old('body_en', $section->body_en ?? '') }}</textarea></div>
<div><label>Body (SW)</label><textarea name="body_sw">{{ old('body_sw', $section->body_sw ?? '') }}</textarea></div>
</div>
<div class="form-grid two">
<div><label>Link</label><input type="text" name="link" value="{{ old('link', $section->link ?? '') }}"></div>
<div><label>Sort order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $section->sort_order ?? 0) }}"></div>
</div>
