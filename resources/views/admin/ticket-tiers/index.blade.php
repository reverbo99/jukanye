@extends('layouts.admin')
@section('title', 'Ticket tiers')
@section('heading', 'Ticket tiers')
@section('content')
<div class="page-head">
    <h1>Ticket tiers</h1>
    <a class="btn btn-accent" href="{{ route('admin.ticket-tiers.create') }}">Add</a>
</div>
<div class="admin-card">
<table class="admin-table">
<thead><tr><th>Name</th><th>Price</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse ($tiers as $item)
<tr>
<td><strong>{{ $item->name_en }}</strong><div class="muted">{{ $item->slug }}</div></td><td>{{ number_format($item->price) }} {{ $item->currency }}</td><td><span class="badge {{ $item->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $item->status }}</span></td>
<td class="actions">
<a class="btn btn-ghost" href="{{ route('admin.ticket-tiers.edit', $item) }}">Edit</a>
<form method="POST" action="{{ route('admin.ticket-tiers.destroy', $item) }}" onsubmit="return confirm('Delete?')">
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
<div style="margin-top:1rem;">{{ $tiers->links() }}</div>
</div>
@endsection