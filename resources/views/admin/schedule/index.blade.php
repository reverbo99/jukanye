@extends('layouts.admin')

@section('title', 'Schedule')
@section('heading', 'Schedule')

@section('content')
    <div class="page-head">
        <h1>Schedule</h1>
        <a class="btn btn-accent" href="{{ route('admin.schedule.create') }}">Add item</a>
    </div>
    <div class="admin-card">
        <table class="admin-table">
            <thead>
            <tr>
                <th>When</th>
                <th>Title</th>
                <th>Status</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>
                        {{ optional($item->starts_at)?->format('Y-m-d H:i') }}
                        @if ($item->ends_at)
                            <div class="muted">→ {{ $item->ends_at->format('Y-m-d H:i') }}</div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $item->title_en }}</strong>
                        <div class="muted">{{ $item->location_en }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $item->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $item->status }}</span>
                    </td>
                    <td class="actions">
                        <a class="btn btn-ghost" href="{{ route('admin.schedule.edit', $item) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.schedule.destroy', $item) }}" onsubmit="return confirm('Delete this item?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No schedule items yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:1rem;">{{ $items->links() }}</div>
    </div>
@endsection
