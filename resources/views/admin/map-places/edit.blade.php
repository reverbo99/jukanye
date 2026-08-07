@extends('layouts.admin')
@section('title', 'Edit map place')
@section('heading', 'Edit map place')
@section('content')
<div class="admin-card">
<form method="POST" action="{{ route('admin.map-places.update', $place) }}" class="form-grid">
@csrf @method('PUT')
@include('admin.map-places._form', ['place' => $place])
<div class="actions">
<button class="btn btn-accent" type="submit">Update</button>
<a class="btn btn-ghost" href="{{ route('admin.map-places.index') }}">Cancel</a>
</div>
</form>
</div>
@endsection