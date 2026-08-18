@extends('layouts.admin')
@section('title', 'People')
@section('heading', 'People')
@section('content')
@php
    $currentLabel = $type !== '' ? ($types[$type] ?? 'People') : 'All people';
@endphp
<div class="page-head">
    <h1>{{ $currentLabel }}</h1>
    <a class="btn btn-accent" href="{{ route('admin.people.create', ['type' => $type ?: 'speaker']) }}">Add person</a>
</div>
<p class="muted" style="margin:-0.35rem 0 1rem">
    Edit speakers, artists, heroes, exhibitions, and friends shown on the website and app. Use <strong>Edit</strong> on a row, then <strong>Update</strong> to save.
</p>
<div class="actions" style="margin-bottom:1rem;">
@foreach($types as $key => $label)
<a class="btn {{ $type === $key ? 'btn-accent' : 'btn-ghost' }}" href="{{ route('admin.people.index', ['type' => $key]) }}">{{ $label }}</a>
@endforeach
<a class="btn {{ $type === '' ? 'btn-accent' : 'btn-ghost' }}" href="{{ route('admin.people.index') }}">All</a>
</div>
<div class="admin-card">
<table class="admin-table">
<thead><tr><th>Name</th><th>Type</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse ($people as $item)
<tr>
<td><strong>{{ $item->name }}</strong><div class="muted">{{ $item->subtitle_en }}</div></td>
<td>{{ $types[$item->type] ?? $item->type }}</td>
<td><span class="badge {{ $item->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $item->status }}</span></td>
<td class="actions">
<a class="btn btn-accent" href="{{ route('admin.people.edit', ['person' => $item->id, 'type' => $type ?: $item->type]) }}">Edit</a>
<form method="POST" action="{{ route('admin.people.destroy', ['person' => $item->id]) }}" onsubmit="return confirm('Delete {{ $item->name }}?')">@csrf @method('DELETE')<input type="hidden" name="type" value="{{ $type }}"><button class="btn btn-danger" type="submit">Delete</button></form>
</td>
</tr>
@empty
<tr><td colspan="4" class="muted">No people in this list yet. Click Add to create one.</td></tr>
@endforelse
</tbody>
</table>
<div style="margin-top:1rem;">{{ $people->links() }}</div>
</div>
@endsection
