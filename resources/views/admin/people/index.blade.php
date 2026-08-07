@extends('layouts.admin')
@section('title', 'People')
@section('heading', 'People')
@section('content')
<div class="page-head">
    <h1>People</h1>
    <a class="btn btn-accent" href="{{ route('admin.people.create', ['type' => $type ?: 'speaker']) }}">Add</a>
</div>
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
<td>{{ $item->type }}</td>
<td><span class="badge {{ $item->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $item->status }}</span></td>
<td class="actions">
<a class="btn btn-ghost" href="{{ route('admin.people.edit', $item) }}">Edit</a>
<form method="POST" action="{{ route('admin.people.destroy', $item) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Delete</button></form>
</td>
</tr>
@empty
<tr><td colspan="4" class="muted">No people yet.</td></tr>
@endforelse
</tbody>
</table>
<div style="margin-top:1rem;">{{ $people->links() }}</div>
</div>
@endsection