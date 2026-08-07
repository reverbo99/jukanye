@extends('layouts.admin')
@section('title', 'Home sections')
@section('heading', 'Home sections')
@section('content')
<div class="page-head">
    <h1>Home sections</h1>
    <a class="btn btn-accent" href="{{ route('admin.home-sections.create') }}">Add</a>
</div>
<div class="admin-card">
<table class="admin-table">
<thead><tr><th>Type</th><th>Title</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse ($sections as $item)
<tr>
<td>{{ $item->type }}</td><td><strong>{{ $item->title_en }}</strong></td><td><span class="badge {{ $item->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $item->status }}</span></td>
<td class="actions">
<a class="btn btn-ghost" href="{{ route('admin.home-sections.edit', $item) }}">Edit</a>
<form method="POST" action="{{ route('admin.home-sections.destroy', $item) }}" onsubmit="return confirm('Delete?')">
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
<div style="margin-top:1rem;">{{ $sections->links() }}</div>
</div>
@endsection