@extends('layouts.admin')
@section('title', 'Media & Sliders')
@section('heading', 'Media & Sliders')
@section('content')
<div class="page-head">
    <h1>Media &amp; Sliders</h1>
    <a class="btn btn-accent" href="{{ route('admin.site-media.create', $currentSlot ? ['slot' => $currentSlot] : []) }}">Add item</a>
</div>

<p class="muted" style="margin:-0.5rem 0 1rem">Manage hero sliders, banner images, YouTube videos, and gallery media for the app and website.</p>

<div class="admin-card" style="margin-bottom:1rem;padding:1rem">
    <form method="GET" action="{{ route('admin.site-media.index') }}" style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:end">
        <div>
            <label>Filter by placement</label>
            <select name="slot" onchange="this.form.submit()">
                <option value="">All placements</option>
                @foreach($slots as $value => $label)
                    <option value="{{ $value }}" @selected($currentSlot === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </form>
</div>

<div class="admin-card">
<table class="admin-table">
<thead>
<tr>
    <th>Preview</th>
    <th>Placement</th>
    <th>Type</th>
    <th>Title</th>
    <th>Order</th>
    <th>Status</th>
    <th></th>
</tr>
</thead>
<tbody>
@forelse ($items as $item)
<tr>
    <td style="width:88px">
        @if($item->kind === 'youtube' && $item->youtubeThumbnail())
            <img src="{{ $item->youtubeThumbnail() }}" alt="" style="width:72px;height:48px;object-fit:cover;border-radius:6px">
        @elseif($item->image)
            <img src="{{ asset('storage/'.$item->image) }}" alt="" style="width:72px;height:48px;object-fit:cover;border-radius:6px">
        @else
            <span class="muted">—</span>
        @endif
    </td>
    <td><span class="badge">{{ $slots[$item->slot] ?? $item->slot }}</span></td>
    <td>{{ $item->kind === 'youtube' ? 'YouTube' : 'Image' }}</td>
    <td>
        <strong>{{ $item->title_en ?: ($item->kind === 'youtube' ? 'Video' : 'Image') }}</strong>
        @if($item->youtube_url)
            <div class="muted" style="font-size:.8rem;max-width:220px;overflow:hidden;text-overflow:ellipsis">{{ $item->youtube_url }}</div>
        @endif
    </td>
    <td>{{ $item->sort_order }}</td>
    <td><span class="badge {{ $item->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $item->status }}</span></td>
    <td class="actions">
        <a class="btn btn-ghost" href="{{ route('admin.site-media.edit', ['siteMediaItem' => $item->id]) }}">Edit</a>
        <form method="POST" action="{{ route('admin.site-media.destroy', ['siteMediaItem' => $item->id]) }}" onsubmit="return confirm('Delete this media item?')">
            @csrf @method('DELETE')
            <button class="btn btn-danger" type="submit">Delete</button>
        </form>
    </td>
</tr>
@empty
<tr><td colspan="7" class="muted">No media items yet. Add hero slider images or YouTube URLs above.</td></tr>
@endforelse
</tbody>
</table>
<div style="margin-top:1rem;">{{ $items->links() }}</div>
</div>
@endsection
