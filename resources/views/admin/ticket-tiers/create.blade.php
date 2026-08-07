@extends('layouts.admin')
@section('title', 'New ticket tier')
@section('heading', 'New ticket tier')
@section('content')
<div class="admin-card">
<form method="POST" action="{{ route('admin.ticket-tiers.store') }}" class="form-grid">
@csrf
@include('admin.ticket-tiers._form')
<div class="actions">
<button class="btn btn-accent" type="submit">Save</button>
<a class="btn btn-ghost" href="{{ route('admin.ticket-tiers.index') }}">Cancel</a>
</div>
</form>
</div>
@endsection