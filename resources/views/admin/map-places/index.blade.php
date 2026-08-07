@extends('layouts.admin')
@section('title', 'Map places')
@section('heading', 'Map places')
@section('content')
<div class="page-head">
    <h1>Map places</h1>
    <a class="btn btn-accent" href="{{ route('admin.map-places.create') }}">Add</a>
</div>
<div class="admin-card">
<table class="admin-table">
<thead><tr><th>Name</th><th>Coords</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse ($places as $item)
<tr>
<td><strong>{{ $item->name_en }}</strong></td><td>{{ $item->lat }}, {{ $item->lng }}</td><td><span class="badge {{ $item->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $item->status }}</span></td>
<td class="actions">
<a class="btn btn-ghost" href="{{ route('admin.map-places.edit', $item) }}">Edit</a>
<form method="POST" action="{{ route('admin.map-places.destroy', $item) }}" onsubmit="return confirm('Delete?')">
@csrf @method('DELETE')
<button class="btn btn-danger" type="submit">Delete</button>
</form>
</td>
</tr>
@empty
<tr><td colspan="8" class="muted">No items yet.</td></tr>
@endforelse
</tbody>
</table>
<div style="margin-top:1rem;">{{ $places->links() }}</div>
</div>
@endsection