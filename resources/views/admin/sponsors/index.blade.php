@extends('layouts.admin')
@section('title', 'Sponsors')
@section('heading', 'Sponsors')
@section('content')
<div class="page-head">
    <h1>Sponsors</h1>
    <a class="btn btn-accent" href="{{ route('admin.sponsors.create') }}">Add</a>
</div>
<div class="admin-card">
<table class="admin-table">
<thead><tr><th>Name</th><th>Tier</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse ($sponsors as $item)
<tr>
<td><strong>{{ $item->name }}</strong></td><td>{{ $item->tier ?: '—' }}</td><td><span class="badge {{ $item->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $item->status }}</span></td>
<td class="actions">
<a class="btn btn-ghost" href="{{ route('admin.sponsors.edit', $item) }}">Edit</a>
<form method="POST" action="{{ route('admin.sponsors.destroy', $item) }}" onsubmit="return confirm('Delete?')">
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
<div style="margin-top:1rem;">{{ $sponsors->links() }}</div>
</div>
@endsection