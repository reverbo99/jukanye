@extends('layouts.admin')
@section('title', 'Form submissions')
@section('heading', 'Form submissions')
@section('content')
<div class="actions" style="margin-bottom:1rem;">
<a class="btn {{ $form === 'register' ? 'btn-accent' : 'btn-ghost' }}" href="{{ route('admin.submissions.index', ['form' => 'register']) }}">Register</a>
<a class="btn {{ $form === 'contact' ? 'btn-accent' : 'btn-ghost' }}" href="{{ route('admin.submissions.index', ['form' => 'contact']) }}">Contact</a>
</div>
<div class="admin-card">
<table class="admin-table">
<thead><tr><th>When</th><th>Email</th><th></th></tr></thead>
<tbody>
@forelse ($submissions as $item)
<tr>
<td>{{ $item->created_at?->format('Y-m-d H:i') }}</td>
<td>{{ $item->email ?: '—' }}</td>
<td><a class="btn btn-ghost" href="{{ route('admin.submissions.show', $item) }}">View</a></td>
</tr>
@empty
<tr><td colspan="3" class="muted">No submissions yet.</td></tr>
@endforelse
</tbody>
</table>
<div style="margin-top:1rem;">{{ $submissions->links() }}</div>
</div>
@endsection