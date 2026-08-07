@extends('layouts.admin')
@section('title', 'Edit ticket tier')
@section('heading', 'Edit ticket tier')
@section('content')
<div class="admin-card">
<form method="POST" action="{{ route('admin.ticket-tiers.update', $tier) }}" class="form-grid">
@csrf @method('PUT')
@include('admin.ticket-tiers._form', ['tier' => $tier])
<div class="actions">
<button class="btn btn-accent" type="submit">Update</button>
<a class="btn btn-ghost" href="{{ route('admin.ticket-tiers.index') }}">Cancel</a>
</div>
</form>
</div>
@endsection