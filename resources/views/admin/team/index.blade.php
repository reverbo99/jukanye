@extends('layouts.admin')
@section('title', 'Team members')
@section('heading', 'Team members')
@section('content')
<div class="page-head">
    <h1>Team members</h1>
    <a class="btn btn-accent" href="{{ route('admin.team.create') }}">Add</a>
</div>
<div class="admin-card">
<table class="admin-table">
<thead><tr><th>Name</th><th>Role</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse ($members as $item)
<tr>
<td><strong>{{ $item->name }}</strong></td><td>{{ $item->role_en ?: '—' }}</td><td><span class="badge {{ $item->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $item->status }}</span></td>
<td class="actions">
<a class="btn btn-ghost" href="{{ route('admin.team.edit', $item) }}">Edit</a>
<form method="POST" action="{{ route('admin.team.destroy', $item) }}" onsubmit="return confirm('Delete?')">
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
<div style="margin-top:1rem;">{{ $members->links() }}</div>
</div>
@endsection