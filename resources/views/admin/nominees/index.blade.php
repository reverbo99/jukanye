@extends('layouts.admin')

@section('title', 'Nominees')
@section('heading', 'Award Nominees')

@section('content')
    <div class="page-head">
        <h1>Nominees</h1>
        <a class="btn btn-accent" href="{{ route('admin.nominees.create') }}">Add nominee</a>
    </div>
    <div class="admin-card">
        <table class="admin-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Status</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse ($nominees as $nominee)
                <tr>
                    <td>
                        <strong>{{ $nominee->name }}</strong>
                        <div class="muted">{{ $nominee->country }}</div>
                    </td>
                    <td>{{ $nominee->category?->name_en ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $nominee->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $nominee->status }}</span>
                    </td>
                    <td class="actions">
                        <a class="btn btn-ghost" href="{{ route('admin.nominees.edit', $nominee) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.nominees.destroy', $nominee) }}" onsubmit="return confirm('Delete this nominee?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No nominees yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:1rem;">{{ $nominees->links() }}</div>
    </div>
@endsection
