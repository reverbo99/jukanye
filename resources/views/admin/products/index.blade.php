@extends('layouts.admin')
@section('title', 'Products')
@section('heading', 'Products')
@section('content')
<div class="page-head">
    <h1>Products</h1>
    <a class="btn btn-accent" href="{{ route('admin.products.create') }}">Add</a>
</div>
<div class="admin-card">
<table class="admin-table">
<thead><tr><th>Name</th><th>Price</th><th>Status</th><th></th></tr></thead>
<tbody>
@forelse ($products as $item)
<tr>
<td><strong>{{ $item->name_en }}</strong><div class="muted">{{ $item->name_sw }}</div></td><td>{{ number_format($item->price) }} {{ $item->currency }}</td><td><span class="badge {{ $item->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $item->status }}</span></td>
<td class="actions">
<a class="btn btn-ghost" href="{{ route('admin.products.edit', $item) }}">Edit</a>
<form method="POST" action="{{ route('admin.products.destroy', $item) }}" onsubmit="return confirm('Delete?')">
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
<div style="margin-top:1rem;">{{ $products->links() }}</div>
</div>
@endsection